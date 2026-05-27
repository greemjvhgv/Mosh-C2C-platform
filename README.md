# Mosh-C2C-platform
A mock C2C Platform I built for a school project
---
title: MOSH ZA - C2C E-commerce Platform
description: A Consumer-to-Consumer (C2C) e-commerce marketplace prototype developed as an educational simulation.
---

# MOSH ZA - C2C E-commerce Platform
https://mosh.gamer.gd/index.php?i=1
MOSH ZA is a Consumer-to-Consumer (C2C) e-commerce marketplace prototype developed as an educational simulation. The platform enables users to register as either buyers or sellers to participate in a simulated digital economy. Buyers can browse products using sophisticated filtering—including category, price, and location-based sorting—while sellers utilize a personalized dashboard to list inventory, track stock levels, and manage incoming orders. To ensure system security and community standards, the platform includes a secure administrative portal for moderating users and resolving reports.

**IMPORTANT: This is a mock platform for demonstration and educational purposes only. Do NOT use real personal information, credit card details, or sensitive data on this site. No real transactions occur, and no physical items will be delivered.**

## Features
-   **User Authentication**: Secure registration, login, and logout functionality with `password_hash` for password storage.
-   **Role-Based Access**: Distinct experiences for Buyers, Sellers, and Administrators.
-   **Product Browsing**: Explore products with advanced filtering options by search term, category, location, and price.
-   **Shopping Cart**: Session-based cart management for adding and removing products.
-   **Simulated Checkout**: A complete, simulated checkout process for placing orders.
-   **Seller Dashboard**: Dedicated portal for sellers to add, edit, and delete product listings, track stock levels, and manage incoming orders (mark as shipped/cancel).
-   **Admin Panel**: Comprehensive administrative interface for managing users (edit, ban, delete), products, and resolving user-submitted reports.
-   **Public Seller Profiles**: View individual seller stores with their listed products and a "Report User" feature.
-   **Responsive Design**: The interface is designed to be accessible and functional across various devices.

## Tech Stack

-   **Frontend**: HTML5, CSS3, Vanilla JavaScript
-   **Backend**: PHP (Procedural/Mixed style)
-   **Database**: MySQL (via `mysqli` extension)
-   **Server Environment**: MAMP (or similar LAMP/WAMP stack)

## Security Considerations (For Educational Purposes)

As this is a prototype, several security vulnerabilities have been identified and are areas for improvement in a production environment:

-   **SQL Injection**: While `login.php` and `register.php` use prepared statements, other files (e.g., `admin.php`, `dashboard.php`, `process_checkout.php`, `profile.php`, `products.php`) still rely on `mysqli_real_escape_string` or direct string interpolation, which are susceptible to SQL injection.
-   **Cross-Site Scripting (XSS)**: User-generated content (e.g., product descriptions, addresses) is not consistently sanitized with `htmlspecialchars()` before being outputted, posing an XSS risk.
-   **Direct File Uploads**: Image uploads in `dashboard.php` and `admin.php` lack strict MIME-type validation and robust filename sanitization, which could lead to arbitrary file uploads.
-   **Cross-Site Request Forgery (CSRF)**: State-changing actions (e.g., deleting a product or user in `admin.php`, shipping/cancelling orders in `dashboard.php`) are triggered via GET requests, making them vulnerable to CSRF attacks.

## License

This project is open-source and available under the MIT License.
```
