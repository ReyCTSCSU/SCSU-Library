# 📚 SCSU Library Management System

A full-stack web-based library management system built using **PHP, MySQL (MariaDB), and Apache (XAMPP)**. This system supports **students and librarians** with role-based access, real-time book availability, notifications, activity logging, and inventory control.

---

## ✅ Technologies Used
- PHP (Backend)
- MySQL / MariaDB (Database)
- Apache (Web Server)
- HTML, CSS (Frontend)
- XAMPP (Local hosting environment)

---

## ✅ User Roles
- **Students**
  - Browse and search books
  - Borrow and return books
  - Add books to favorites
  - View notifications
- **Librarians**
  - Add, edit, activate, and deactivate books
  - View system activity logs
  - Receive low-stock and out-of-stock alerts

---

## 📄 Pages & Features (By Page)

### 🔹 `login.php`
- User login form
- Authenticates users using email and password
- Redirects users to the dashboard after login

### 🔹 `register.php`
- New user registration form
- Allows users to select:
  - Student or Librarian role
- Validates:
  - Unique user ID
  - Unique email
- Redirects to login page after successful registration

---

### 🔹 `dashboard.php`
- Role-based dashboard display  
- **Student Dashboard Features:**
  - Total borrowed books
  - Next due date
  - Total favorites
  - Total notifications
- **Librarian Dashboard Features:**
  - Total books in system
  - Active borrowings
  - Out-of-stock count
  - Recent activity log preview

---

### 🔹 `books.php` (Book Catalog)
- Displays all books in the system
- **Search functionality** (by title and author)
- **Sorting options:**
  - Title A–Z / Z–A
  - Oldest → Newest
  - Available first
- **Visibility rules:**
  - Students only see **active books**
  - Librarians see **all books**
- **Status badges:**
  - Available
  - Out of Stock
  - Inactive
- **Librarian Controls:**
  - Edit button
  - Activate / Deactivate buttons (with confirmation)

---

### 🔹 `book_details.php`
- Displays full book details:
  - Title, Author, ISBN
  - Genre, Year, Location
  - Available copies
- **Student Actions:**
  - Borrow book
  - Add / remove favorite
- Prevents borrowing if:
  - Book is out of stock
  - Book is inactive
- Automatically creates:
  - Student notification on borrow
  - Librarian notifications for:
    - Low stock
    - Out of stock

---

### 🔹 `borrowings.php`
- Shows:
  - Active student borrowings
  - Past return history
- Allows student to:
  - Return books
- Updates inventory when books are returned
- Creates return notifications for students

---

### 🔹 `favorites.php`
- Displays all books favorited by the student
- Allows removal of favorites

---

### 🔹 `notifications.php`
- Displays all notifications for the logged-in user
- Supports:
  - Individual delete
  - Clear all notifications
- Librarians receive:
  - Low stock alerts
  - Out of stock alerts
  - System messages

---

### 🔹 `add_book.php` (Librarian Only)
- Form to add new books to the system
- Automatically sets:
  - Quantity available
  - Active status
- Logs action to:
  - Activity log
- Sends notification to librarian who added the book

---

### 🔹 `edit_book.php` (Librarian Only)
- Allows editing:
  - Title, Author, ISBN
  - Genre, Year, Location
  - Total quantity
- Automatically recalculates:
  - Available quantity
  - Stock status
- Logs edit action to:
  - Activity log
- Includes back button to book catalog

---

### 🔹 `activity_log.php` (Librarian Only)
- Displays all system actions:
  - Added books
  - Edited books
  - Activated/deactivated books
- Shows:
  - Librarian name
  - Book title
  - Timestamp
  - Action details

---

### 🔹 `profile.php`
- Displays user's:
  - Full name
  - Email
  - Role

---

### 🔹 `logout.php`
- Destroys session
- Logs the user out safely

---

## 🗄️ Database Structure
The system uses the following core tables:
- `Users`
- `Books`
- `Borrowings`
- `Favorites`
- `Notifications`
- `Activity_Log`

---

## ✅ Key System Features
- Full authentication system
- Role-based access control
- Real-time inventory tracking
- Borrow & return system
- Low-stock and out-of-stock alerts
- Book activation & deactivation
- Activity logging
- Favorites system
- Search & sorting
- Clean UI with modern card-based layout

---

## ✅ Setup Instructions
1. Install **XAMPP**
2. Start **Apache** and **MySQL**
3. Import the provided `create_schema.sql` into phpMyAdmin
4. Place project folder inside: xampp\htdocs\
