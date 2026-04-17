++++++++++++++ Hotel Management System --------------------

A full-featured Hotel Management System designed to manage hotel operations efficiently. This system supports two main roles: Admin and Customer, with essential features like room booking, management, and billing.

-- Features
------> Admin Role
Dashboard overview (rooms, bookings, users)
Manage rooms (Add / Edit / Delete)
Manage customers
View and manage bookings
Update booking status (Check-in / Check-out)
Generate reports
Manage pricing and room types

------> Customer Role
Register and login
View available rooms
Book rooms
View booking history
Cancel bookings
Update profile

------> System Functionalities
Authentication & Authorization (Admin / Customer)
Room availability checking
Booking system with dates
Billing and invoice generation
Search and filter rooms
Responsive UI design
Database integration

----- Technologies Used -----
Frontend
HTML5
CSS3 / Tailwind CSS
JavaScript
Backend
PHP / Java / Spring Boot (choose your stack)
REST API (if applicable)
Database
MySQL / PostgreSQL
Tools
Git & GitHub
XAMPP / Apache / Tomcat
VS Code / IntelliJ IDEA

📂 Project Structure
Hotel-Management-System/
│
├── src/
│   ├── controllers/
│   ├── models/
│   ├── views/
│
├── config/
│   └── database connection
│
├── public/
│   ├── css/
│   ├── js/
│   └── images/
│
├── database/
│   └── SQL files
│
└── README.md
--> Use this for standard project structure setup.
also can write this less files in root of folder structure.

🧑‍💻 Installation & Setup
1. Clone the Repository
git clone https://github.com/your-username/hotel-management-system.git

3. Navigate to Project
cd hotel-management-system

5. Setup Database
Create a database (e.g. hotel_management)
Import SQL file from /database folder

7. Configure Database Connection

Update your config file:

$host = "localhost";
$user = "root";
$password = "";
$dbname = "php_hotel_ms";

5. Run the Project
Start Apache & MySQL (XAMPP)
Open browser:
http://localhost/hotel-management-system

🔐 Default Login (Optional)
Admin
Email: admin@gmail.com
Password: admin123 or Register new as admin and modified code to disable admin login as
Customer
Register a new account

<img width="2367" height="1408" alt="Screenshot 2026-04-17 072833" src="https://github.com/user-attachments/assets/fca7018c-96db-4ef6-a685-3b3863d6ff79" />
<img width="2399" height="1427" alt="Screenshot 2026-04-17 072743" src="https://github.com/user-attachments/assets/4dc00462-096e-4683-817f-ef6ec7128ea2" />

🚀 Future Improvements
Online payment integration
Email notifications
Mobile app version
Advanced analytics dashboard
Multi-language support
--+ Contributing +--

Contributions are welcome!

Fork the project
Create your feature branch
Commit your changes
Push to the branch
Open a Pull Request
📄 License

This project is licensed under the MIT License.
