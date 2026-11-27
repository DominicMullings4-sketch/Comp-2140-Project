import tkinter as tk
from tkinter import ttk, messagebox
import json
import os
import datetime

# --- Configuration ---
FOLDER_NAME = "Feedback demo"
FILE_NAME = "feedback book.json"
FILE_PATH = os.path.join(FOLDER_NAME, FILE_NAME)

class App:
    def __init__(self, root):
        self.root = root
        self.root.title("Data viewer")
        self.root.geometry("600x400")

        # Ensure folder/file exists
        if not os.path.exists(FOLDER_NAME):
            os.makedirs(FOLDER_NAME)
        if not os.path.exists(FILE_PATH):
            with open(FILE_PATH, "w") as f:
                json.dump([], f)

        # 1. Create the Treeview (Table)
        # We define the columns and the headings
        columns = ("id", "user", "choice", "reason", "details")
        self.tree = ttk.Treeview(root, columns=columns, show="headings")
        
        # Define what each column header says and how wide it is
        self.tree.heading("id", text="ID", command=lambda: self.sort_column("id"))
        self.tree.heading("user", text="User", command=lambda: self.sort_column("user"))
        self.tree.heading("choice", text="Status", command=lambda: self.sort_column("choice"))
        self.tree.heading("reason", text="Reason", command=lambda: self.sort_column("reason"))
        self.tree.heading("details", text="Details")
        
        # Set column widths
        self.tree.column("id", width=50)
        self.tree.column("user", width=100)
        self.tree.column("choice", width=80)
        self.tree.column("reason", width=100)
        self.tree.column("details", width=250)

        self.tree.pack(expand=True, fill="both", padx=10, pady=10)

        # 2. Button to Refresh Data
        btn_refresh = tk.Button(root, text="Refresh Table", command=self.load_data)
        btn_refresh.pack(pady=5)

        # Initial Load
        self.load_data()

    def load_data(self):
        """Reads JSON and fills the Treeview"""
        # Clear current table rows
        for item in self.tree.get_children():
            self.tree.delete(item)

        # Load from file
        try:
            with open(FILE_PATH, "r") as f:
                data = json.load(f)
                for entry in data:
                    # Insert row into table
                    self.tree.insert("", tk.END, values=(
                        entry.get("ID", "N/A"),
                        entry.get("user", "N/A"),
                        entry.get("status", "N/A"),
                        entry.get("reason", "N/A"),
                        entry.get("addinfo", "N/A")
                    ))
        except Exception as e:
            print(f"Error loading: {e}")

    def sort_column(self, col):
        """Sorts the table based on the clicked header"""
        # Get all current data from the tree
        data = []
        for item in self.tree.get_children():
            data.append(list(self.tree.item(item)["values"]))

        # Sort the data list
        # We use .lower() to make sure 'Apple' and 'apple' are sorted together
        data.sort(key=lambda x: str(x[0]).lower() if col == "id" else str(x[1]).lower() if col == "user" else str(x[2]).lower() if col == "choice" else str(x[3]).lower() if col == "reason" else str(x[4]).lower())

        # Clear and Re-insert
        for item in self.tree.get_children():
            self.tree.delete(item)
        for row in data:
            self.tree.insert("", tk.END, values=row)

if __name__ == "__main__":
    root = tk.Tk()
    app = App(root)
    root.mainloop()
