# Laravel REST API - CRUD Operations

This Laravel REST API provides CRUD operations for managing Companies, Users (Employees), Customers, and Orders. It also includes features like User Authentication, Activity Logs, and Search Functionality.

## Technologies Used

- PHP
- Composer
- Laravel
- Postman
- MySQL

## API Overview

This API supports the following core features:

- User Management
- Company Management
- Customer Management
- Order Management
- Authentication (JWT)
- Search Functionality
- User Activity Logs

## Endpoints

### Companies

1.  **Get All Companies**

    -   `GET /api/companies`
    -   Retrieve all companies.

2.  **Get Single Company**

    -   `GET /api/companies/{company_id}`
    -   Retrieve a specific company by ID.

3.  **Add a Company**

    -   `POST /api/companies`
    -   Create a new company.
    -   Sample Data:

        ```json
        {
          "company_name": "ABC Corp",
          "contact_name": "John Doe",
          "contact_phone": "123-456-7890",
          "contact_email": "john@abc.com"
        }
        ```

4.  **Update a Company**

    -   `PUT /api/companies/{company_id}`
    -   Update an existing company.

5.  **Delete a Company**

    -   `DELETE /api/companies/{company_id}`
    -   Delete a company by ID.

### Users (Employees)

1.  **Get All Users**

    -   `GET /api/users`
    -   Retrieve all users.

2.  **Get Single User**

    -   `GET /api/users/{user_id}`
    -   Retrieve a specific user by ID.

3.  **Add a User**

    -   `POST /api/users`
    -   Add a new user to the system.
    -   Sample Data:

        ```json
        {
          "name": "Jane",
          "surname": "Doe",
          "email": "jane@company.com",
          "role": "employee",
          "company_id": 1
        }
        ```

4.  **Update a User**

    -   `PUT /api/users/{user_id}`
    -   Update user information.

5.  **Delete a User**

    -   `DELETE /api/users/{user_id}`
    -   Remove a user from the system.

6.  **Get User Activity Logs**

    -   `GET /api/users/{user_id}/activity_logs`
    -   Retrieve activity logs for a specific user.

7.  **Get User's Orders**

    -   `GET /api/users/{user_id}/orders`
    -   Retrieve all orders placed by a specific user.

### Customers

1.  **Get All Customers**

    -   `GET /api/customers`
    -   Retrieve a list of all customers.

2.  **Get Single Customer**

    -   `GET /api/customers/{customer_id}`
    -   Retrieve a specific customer by ID.

3.  **Add a Customer**

    -   `POST /api/customers`
    -   Create a new customer.
    -   Sample Data:

        ```json
        {
          "first_name": "Michael",
          "last_name": "Smith",
          "email": "michael@customer.com",
          "phone": "987-654-3210"
        }
        ```

4.  **Update a Customer**

    -   `PUT /api/customers/{customer_id}`
    -   Update customer details.

5.  **Delete a Customer**

    -   `DELETE /api/customers/{customer_id}`
    -   Remove a customer from the system.

### Orders

1.  **Get Orders by Customer**

    -   `GET /api/customers/{customer_id}/orders`
    -   Retrieve all orders for a specific customer.

2.  **Create an Order**

    -   `POST /api/orders`
    -   Add a new order for a customer.
    -   Sample Data:

        ```json
        {
          "customer_id": 1,
          "order_date": "2025-03-04",
          "total_amount": 200,
          "status": "pending"
        }
        ```

### Authentication

1.  **Login**

    -   `POST /api/login`
    -   Retrieve JWT token for authentication.
    -   Sample Data:

        ```json
        {
          "email": "user@example.com",
          "password": "yourpassword"
        }
        ```

2.  **Token Validation**

    -   `GET /api/user`
    -   Validate the JWT token and retrieve user details.

### Search Functionality

1.  **Search Users**

    -   `GET /api/users/search`
    -   Search for users by name, email, or other parameters.
    -   Sample Query:
        -   `/api/users/search?name=John`

## Postman Collection

To test the endpoints, you can use Postman. Below are examples of how to make requests:

**Example 1: Create a Company**

-   Method: `POST`
-   URL: `http://localhost:8000/api/companies`
-   Body:

    ```json
    {
      "company_name": "XYZ Ltd",
      "contact_name": "Alice Cooper",
      "contact_phone": "111-222-3333",
      "contact_email": "alice@xyz.com"
    }
    ```

**Example 2: Get Orders by Customer**

-   Method: `GET`
-   URL: `http://localhost:8000/api/customers/1/orders`

**Example 3: Update a User**

-   Method: `PUT`
-   URL: `http://localhost:8000/api/users/1`
-   Body:

    ```json
    {
      "name": "Updated Name",
      "surname": "Updated Surname",
      "role": "manager"
    }
    ```

**Example 4: Delete a Customer**

-   Method: `DELETE`
-   URL: `http://localhost:8000/api/customers/1`

## Notes

-   JWT token is required for authentication.
-   All `POST` and `PUT` requests must include a JSON body.
-   Additional features such as Order Management, User Activity Logs, and Search Functionality are available.

This README provides an overview of the available API endpoints for managing Companies, Users, Customers, and Orders, along with example Postman queries to test them. You can easily interact with the system via Postman or any other API testing tool.
