import tkinter as tk
from tkinter import ttk 
import json
import os


FOLDER_NAME = "Feedback demo"






def ensure_folder_exists():
    """Creates the folder if it doesn't exist yet."""
    if not os.path.exists(FOLDER_NAME):
        os.makedirs(FOLDER_NAME)


def save2file():
    file_name = "feedback book.json"


    # creates the full path 
    # handles the slashes correctly regardless of os
    FILE_PATH = os.path.join(FOLDER_NAME, file_name)


    data_entry = {
        "ID": entry1.get(),
        "status": status.get(),
        "reason": rcstatus.get(),
        "addinfo": addinfo.get("1.0", tk.END).strip()
    }
    
    try:
        # 2. Read the existing data first (if the file exists)
        # appends to list
        data_list = []
        if os.path.exists(FILE_PATH):
            with open(FILE_PATH, "r") as f:
                data_list = json.load(f)

        data_list.append(data_entry)

        # saves modified list
        with open(FILE_PATH, "w") as f:
            json.dump(data_list, f, indent=4)
        
        label_result.config(text=f"Feedback saved successfully :)")
    except Exception as e:
        label_result.config(text=f"didnt work")
    #






def on_select():
    
    status_is = status.get() # Get the current value of the selection
    rcstatus_is = rcstatus.get()
    id_is = entry1.get()

    print(f"Delivery {status_is}, rc {rcstatus_is}")
    # label_result.config(text=f"You chose {status_is}, and reason code is {rcstatus_is}")
    noissues = 0

    if id_is == "":
        label_result.config(text=f"Please enter an ID")
        noissues = 0
    elif status_is == options[1]:
        # completed, no reason code needed
        rcstatus_is = reasoncode[0]
        #label_result.config(text=f"The status is '{status_is}'. Reason code defaults to {rcstatus_is}")
        noissues = 1
    else:
        #failed, need a reason code
        #rcstatus_is = reasoncode[0]

        if rcstatus_is == "0":
            label_result.config(text=f"reason code cannot be {rcstatus_is} when status is {status_is}.")
            noissues = 0 # redundant?

        else:           
            # label_result.config(text=f"'{status_is}', and reason code is {rcstatus_is}")
            #addinfotext = addinfo.get("1.0", tk.END).strip()
            #print(addinfotext)
            noissues = 1

    if noissues == 1:
        # save
        save2file()
        
        




root = tk.Tk()
root.title("Feedback")
root.geometry("500x500")

# tells the window to expand based on the grid
root.grid_rowconfigure(0, weight=1)
root.grid_columnconfigure(0, weight=1)

ensure_folder_exists()



options = ["Failed", "Completed"]

reasoncode = [0, 1, 2, 3]

# tracks what the user clicks.
status = tk.StringVar(value=options[0])

rcstatus = tk.StringVar(value=reasoncode[0])


rownum = 0
# ID
tk.Label(root, text="ID:").grid(row=rownum, column=0, padx=5, pady=10, sticky="w")
entry1 = tk.Entry(root)
entry1.grid(row=rownum, column=0, padx=5, pady=10, sticky="e")


rownum = 1
tk.Label(root, text="Delivery status:").grid(row=rownum, column=0, padx=5, pady=10, sticky="w")
# delstatus
completion_state = tk.OptionMenu(root, status, *options)
completion_state.grid(row=rownum, column=0, padx=20, pady=20, sticky="e")

rownum = 2
tk.Label(root, text="Reason code (0 if delivery completed):").grid(row=rownum, column=0, padx=5, pady=10, sticky="w")
# reasoncode
reason = tk.OptionMenu(root, rcstatus, *reasoncode)
reason.grid(row=rownum, column=0, padx=20, pady=20, sticky="e")

rownum = 3 +1
tk.Label(root, text="Additional info:").grid(row=rownum-1, column=0, padx=5, pady=10, sticky="w")
addinfo = tk.Text(root, wrap="word", font=("Arial", 12), width=100, height=8)
addinfo.grid(row=rownum, column=0, sticky="nsew", padx=20, pady=20)


rownum = 9
# 5. A label to show the result
label_result = tk.Label(root, text="")
label_result.grid(row=rownum, column=0, pady=10)


rownum = 10
# 4. A button to trigger an action
btn = tk.Button(root, text="Submit", command=on_select)
btn.grid(row=rownum, column=0, pady=10)


root.mainloop()
