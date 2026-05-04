<div align="center">

# 🛡️ CSRMS
## Complaint & Service Request Management System

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![XAMPP](https://img.shields.io/badge/XAMPP-Apache-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)](https://apachefriends.org)

**A full-stack web application for managing complaints and service requests
with role-based access for Admin, User, and Technician.**
 

</div>

---

## 👥 Team Members

| Name | GitHub | Role |
|------|--------|------|
| Syed Anas | [@SyedAnas51](https://github.com/SyedAnas51) | Admin Module, Complaints & Reports, Technician Panel |
| Fahad | — | Project Setup, Database, User Panel, Dashboard |

---

## 📌 Project Description

CSRMS is a web-based complaint and service request management system built
using **PHP 8**, **MySQL**, and **Bootstrap 5.3**. It allows users to submit
complaints, technicians to manage and resolve them, and admins to oversee the
entire workflow including reports, escalations, and performance tracking.

The system applies core **Software Engineering Design Patterns** including
MVC, Singleton, Strategy, Observer, and Factory to ensure a clean,
maintainable, and scalable architecture.

---

## 🎯 Technical Focus — Design Pattern Implementation

| Pattern | Implementation |
|---------|---------------|
| **MVC** | Model: DB tables · View: PHP pages · Controller: PHP logic per page |
| **Singleton** | `db_connection.php` — single DB connection shared across all modules |
| **Strategy** | `auth_admin.php`, `auth_user.php`, `auth_technician.php` — role-based auth |
| **Observer** | Status change triggers timestamp, escalation flag, and service note log |
| **Factory** | Login factory creates correct session type based on user role |

---

## ✅ Features — 20/20 Implemented

| # | Feature | Module |
|---|---------|--------|
| 1 | Complaint Submission | User |
| 2 | Category Selection | User / Admin |
| 3 | Image Attachment | User |
| 4 | Complaint Tracking | User |
| 5 | Complaint History | User |
| 6 | Complaint Assignment | Admin |
| 7 | Priority Management | Admin |
| 8 | Status Management | Admin / Technician |
| 9 | Technician Task Dashboard | Technician |
| 10 | Service Notes Entry | Technician |
| 11 | Complaint Completion Recording | Technician |
| 12 | Monthly Complaint Report | Admin |
| 13 | Category-wise Analysis | Admin |
| 14 | Resolution Time Calculation | Admin / Technician |
| 15 | Complaint Filtering | Admin / User |
| 16 | Complaint Search | Admin / User |
| 17 | CSV Export | Admin |
| 18 | Staff Performance Monitoring | Admin |
| 19 | Complaint Escalation | Admin |
| 20 | Complaint Reopening | User / Admin |

---

## 🏗️ Project Structure
