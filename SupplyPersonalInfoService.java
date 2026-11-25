import java.time.Duration;
import java.time.Instant;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.Optional;
import java.util.Scanner;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.atomic.AtomicLong;
import java.util.regex.Pattern;

public class SupplyPersonalInfoService {

    public static final int MAX_NAME_LENGTH = 120;
    public static final int MAX_ADDRESS_LENGTH = 200;
    public static final long MAX_STORAGE_TIME_MS = 3000L;

    private static final Pattern NAME_PATTERN =
            Pattern.compile("^[A-Za-z][A-Za-z .'-]{0,119}$");
    private static final Pattern ADDRESS_ALLOWED_PATTERN =
            Pattern.compile("^[A-Za-z0-9 #\\-,'./]+$");
    private static final Pattern ADDRESS_DISALLOWED_PATTERN =
            Pattern.compile("[!?@$%^*]");
    private static final Pattern PHONE_PATTERN =
            Pattern.compile("^[+]?[0-9()\\s-]{7,20}$");
    private static final Pattern EMAIL_PATTERN =
            Pattern.compile("^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$");

    private final AtomicLong idCounter = new AtomicLong(1L);
    private final Map<Long, PersonalInfo> records = new ConcurrentHashMap<>();
    private final Map<String, Long> emailIndex = new ConcurrentHashMap<>();

    public static class PersonalInfoInput {
        private final String fullName;
        private final String deliveryAddress;
        private final String phoneNumber;
        private final String email;

        public PersonalInfoInput(String fullName, String deliveryAddress,
                                   String phoneNumber, String email) {
            this.fullName = fullName;
            this.deliveryAddress = deliveryAddress;
            this.phoneNumber = phoneNumber;
            this.email = email;
        }

        public String getFullName() { return fullName; }
        public String getDeliveryAddress() { return deliveryAddress; }
        public String getPhoneNumber() { return phoneNumber; }
        public String getEmail() { return email; }
    }

    public static class PersonalInfo {
        private final long id;
        private String fullName;
        private String deliveryAddress;
        private String phoneNumber;
        private String email;
        private Instant updatedAt;

        public PersonalInfo(long id, String fullName, String deliveryAddress,
                            String phoneNumber, String email, Instant updatedAt) {
            this.id = id;
            this.fullName = fullName;
            this.deliveryAddress = deliveryAddress;
            this.phoneNumber = phoneNumber;
            this.email = email;
            this.updatedAt = updatedAt;
        }

        public long getId() { return id; }
        public String getFullName() { return fullName; }
        public String getDeliveryAddress() { return deliveryAddress; }
        public String getPhoneNumber() { return phoneNumber; }
        public String getEmail() { return email; }
        public Instant getUpdatedAt() { return updatedAt; }

        public void applyUpdate(String fullName, String deliveryAddress,
                                String phoneNumber, String email) {
            this.fullName = fullName;
            this.deliveryAddress = deliveryAddress;
            this.phoneNumber = phoneNumber;
            this.email = email;
            this.updatedAt = Instant.now();
        }
    }

    public static class OperationResult {
        private final boolean success;
        private final PersonalInfo record;
        private final List<String> errors;
        private final long elapsedMs;

        private OperationResult(boolean success, PersonalInfo record,
                                List<String> errors, long elapsedMs) {
            this.success = success;
            this.record = record;
            this.errors = errors;
            this.elapsedMs = elapsedMs;
        }

        public static OperationResult ok(PersonalInfo record, long elapsedMs) {
            return new OperationResult(true, record, List.of(), elapsedMs);
        }

        public static OperationResult fail(List<String> errors, long elapsedMs) {
            return new OperationResult(false, null, errors, elapsedMs);
        }

        public boolean isSuccess() { return success; }
        public PersonalInfo getRecord() { return record; }
        public List<String> getErrors() { return errors; }
        public long getElapsedMs() { return elapsedMs; }
    }

    public OperationResult submit(PersonalInfoInput input) {
        Instant start = Instant.now();
        if (input == null) {
            return OperationResult.fail(
                    List.of("input: Input cannot be null."),
                    elapsed(start)
            );
        }

        String name = trimOrNull(input.getFullName());
        String address = trimOrNull(input.getDeliveryAddress());
        String phone = trimOrNull(input.getPhoneNumber());
        String email = trimOrNull(input.getEmail());

        List<String> errors = validate(name, address, phone, email);

        String emailKey = email == null ? null : email.toLowerCase();
        if (errors.isEmpty() && emailKey != null && emailIndex.containsKey(emailKey)) {
            errors.add("email: Already in use.");
        }

        if (!errors.isEmpty()) {
            return OperationResult.fail(errors, elapsed(start));
        }

        long id = idCounter.getAndIncrement();
        Long existingOwner = emailIndex.putIfAbsent(emailKey, id);
        if (existingOwner != null) {
            return OperationResult.fail(
                    List.of("email: Duplicate email conflict."),
                    elapsed(start)
            );
        }

        PersonalInfo info = new PersonalInfo(id, name, address, phone, email, Instant.now());
        records.put(id, info);

        long duration = elapsed(start);
        if (duration > MAX_STORAGE_TIME_MS) {
            emailIndex.remove(emailKey, id);
            records.remove(id);
            return OperationResult.fail(
                    List.of("storage: Exceeded time limit."),
                    duration
            );
        }

        return OperationResult.ok(info, duration);
    }

    public OperationResult update(long id, PersonalInfoInput input) {
        Instant start = Instant.now();
        PersonalInfo existing = records.get(id);
        if (existing == null) {
            return OperationResult.fail(List.of("id: Record not found."), elapsed(start));
        }
        if (input == null) {
            return OperationResult.fail(List.of("input: Input cannot be null."), elapsed(start));
        }

        String name = input.getFullName() == null ? existing.getFullName() : input.getFullName().trim();
        String address = input.getDeliveryAddress() == null ? existing.getDeliveryAddress() : input.getDeliveryAddress().trim();
        String phone = input.getPhoneNumber() == null ? existing.getPhoneNumber() : input.getPhoneNumber().trim();
        String email = input.getEmail() == null ? existing.getEmail() : input.getEmail().trim();

        List<String> errors = validate(name, address, phone, email);

        String newEmailKey = email.toLowerCase();
        String oldEmailKey = existing.getEmail().toLowerCase();
        if (errors.isEmpty() && !newEmailKey.equals(oldEmailKey)) {
            Long owner = emailIndex.get(newEmailKey);
            if (owner != null && !owner.equals(id)) {
                errors.add("email: Already in use.");
            }
        }

        if (!errors.isEmpty()) {
            return OperationResult.fail(errors, elapsed(start));
        }

        synchronized (existing) {
            if (!newEmailKey.equals(oldEmailKey)) {
                emailIndex.remove(oldEmailKey, id);
                emailIndex.put(newEmailKey, id);
            }
            existing.applyUpdate(name, address, phone, email);
        }

        long duration = elapsed(start);
        if (duration > MAX_STORAGE_TIME_MS) {
            return OperationResult.fail(List.of("storage: Exceeded time limit."), duration);
        }

        return OperationResult.ok(existing, duration);
    }

    public Optional<PersonalInfo> getById(long id) {
        return Optional.ofNullable(records.get(id));
    }

    public int count() {
        return records.size();
    }

    private List<String> validate(String name, String address, String phone, String email) {
        List<String> errors = new ArrayList<>();

        if (name == null || name.isEmpty()) {
            errors.add("fullName: Required.");
        } else if (name.length() > MAX_NAME_LENGTH || !NAME_PATTERN.matcher(name).matches()) {
            errors.add("fullName: Invalid.");
        }

        if (address == null || address.isEmpty()) {
            errors.add("deliveryAddress: Required.");
        } else if (address.length() > MAX_ADDRESS_LENGTH
                || ADDRESS_DISALLOWED_PATTERN.matcher(address).find()
                || !ADDRESS_ALLOWED_PATTERN.matcher(address).matches()) {
            errors.add("deliveryAddress: Invalid.");
        }

        if (phone == null || phone.isEmpty()) {
            errors.add("phoneNumber: Required.");
        } else {
            long digits = phone.chars().filter(Character::isDigit).count();
            if (!PHONE_PATTERN.matcher(phone).matches() || digits < 7 || digits > 15) {
                errors.add("phoneNumber: Invalid.");
            }
        }

        if (email == null || email.isEmpty()) {
            errors.add("email: Required.");
        } else if (!EMAIL_PATTERN.matcher(email).matches()) {
            errors.add("email: Invalid.");
        }

        return errors;
    }

    private String trimOrNull(String value) {
        return value == null ? null : value.trim();
    }

    private long elapsed(Instant start) {
        return Duration.between(start, Instant.now()).toMillis();
    }

    public static void main(String[] args) {
        SupplyPersonalInfoService service = new SupplyPersonalInfoService();
        Scanner scanner = new Scanner(System.in);

        while (true) {
            System.out.println("\n1 = Submit | 2 = Update | 3 = View by ID | 0 = Exit");
            String choice = scanner.nextLine().trim();

            if ("0".equals(choice)) {
                break;
            } else if ("1".equals(choice)) {
                System.out.print("Full Name: ");
                String name = scanner.nextLine();
                System.out.print("Delivery Address: ");
                String address = scanner.nextLine();
                System.out.print("Phone Number: ");
                String phone = scanner.nextLine();
                System.out.print("Email: ");
                String email = scanner.nextLine();

                OperationResult res = service.submit(
                        new PersonalInfoInput(name, address, phone, email)
                );
                if (res.isSuccess()) {
                    System.out.println("Saved ID: " + res.getRecord().getId()
                            + " (" + res.getElapsedMs() + " ms)");
                } else {
                    System.out.println("Rejected: " + res.getErrors());
                }
            } else if ("2".equals(choice)) {
                System.out.print("Record ID: ");
                long id = Long.parseLong(scanner.nextLine().trim());
                System.out.print("New Full Name (Enter to keep current): ");
                String name = scanner.nextLine();
                System.out.print("New Delivery Address (Enter to keep current): ");
                String address = scanner.nextLine();
                System.out.print("New Phone Number (Enter to keep current): ");
                String phone = scanner.nextLine();
                System.out.print("New Email (Enter to keep current): ");
                String email = scanner.nextLine();

                OperationResult res = service.update(id, new PersonalInfoInput(
                        name.isEmpty() ? null : name,
                        address.isEmpty() ? null : address,
                        phone.isEmpty() ? null : phone,
                        email.isEmpty() ? null : email
                ));
                if (res.isSuccess()) {
                    System.out.println("Updated ID: " + res.getRecord().getId()
                            + " | Name: " + res.getRecord().getFullName()
                            + " (" + res.getElapsedMs() + " ms)");
                } else {
                    System.out.println("Update rejected: " + res.getErrors());
                }
            } else if ("3".equals(choice)) {
                System.out.print("Record ID: ");
                long id = Long.parseLong(scanner.nextLine().trim());
                service.getById(id).ifPresentOrElse(
                        r -> System.out.println("ID: " + r.getId()
                                + " | Name: " + r.getFullName()
                                + " | Address: " + r.getDeliveryAddress()
                                + " | Phone: " + r.getPhoneNumber()
                                + " | Email: " + r.getEmail()),
                        () -> System.out.println("Record not found.")
                );
            }
        }

        scanner.close();
    }
}
