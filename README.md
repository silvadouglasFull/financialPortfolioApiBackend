# financialPortfolioApiBackend

> Version 1.0.0

---

## Project Objective

This application is a functional interface equivalent to a financial wallet in which users can make balance transfers and deposits. It serves as the backend API for a financial portfolio system.

---

## How to Run the Project 🚀

Follow these steps carefully to get the `financialPortfolioApiBackend` application up and running on your local machine.

### Prerequisites

Before you start, ensure your system meets the following requirements:

1.  **Docker**: Make sure Docker is installed and running on your system.
2.  **Free Ports**: The following ports must be free on your machine:
    -   `8000` (for the API backend)
    -   `3306` (for the MySQL database)
    -   `8080` (for phpMyAdmin)

---

### Setup and Execution Steps

1.  **Download `financialPortfolioDataBases` Project**
    First, you need the companion database project. Download it from the following repository:

    ```bash
    git clone https://github.com/silvadouglasFull/financialPortfolioDataBases.git
    ```

2.  **Configure and Run `financialPortfolioDataBases` Container**
    Navigate into the `financialPortfolioDataBases` project directory, read its `README.md` file to understand how to configure its environment variables, and then execute its Docker container. This project contains the necessary database services.

3.  **Rename Nginx Configuration**
    In the `financialPortfolioApiBackend` project directory, rename the Nginx configuration file:

    ```bash
    mv docker-compose/nginx/dev-travellist.conf docker-compose/nginx/travellist.conf
    ```

4.  **Start `financialPortfolioApiBackend` Containers**
    From the root of your `financialPortfolioApiBackend` project, execute the Docker Compose command to build and start the application's containers in detached mode:

    ```bash
    docker compose up -d --build
    ```

5.  **Run Database Migrations and Seeders**
    Access the shell of the `php:8.4-fpm` container (you might need to find its exact name using `docker ps`). Once inside the container's shell, run the database migrations and then the seeders **in this exact order**:

    ```bash
    docker exec -it <php-fpm-container-name-or-id> bash
    # Inside the container shell:
    composer db:migrate
    composer db:seeder
    ```

    Replace `<php-fpm-container-name-or-id>` with the actual name or ID of your PHP FPM container (e.g., `financial-portfolio-api-backend-php-fpm-1`).

6.  **Access phpMyAdmin and Get User Email**
    Access phpMyAdmin in your browser, which should be running at:
    [http://localhost:8080](https://www.google.com/search?q=http://localhost:8080)
    Log in (default credentials usually `root` / `root` or `root` / no password, depending on your database setup), navigate to your application's database, find any user, and **note down their email address**.

7.  **Access the Application**
    The `financialPortfolioApiBackend` application will now be running and accessible at:
    [http://localhost:8000](https://www.google.com/search?q=http://localhost:8000)

8.  **Log In to the Application**
    Use the **email address you noted from phpMyAdmin** to log in.

    -   If the user is an **admin** type, the default password can be found in `database/seeders/UserSeeder.php` at line 17.
    -   If the user is a **common** type (or any other type), the default password can be viewed in `database/factories/UserFactory.php`.

You should now be able to interact with the API. Enjoy\!
API Documentation for your application.

## Path Table

| Method | Path                                                       | Description                                        |
| ------ | ---------------------------------------------------------- | -------------------------------------------------- |
| GET    | [/google/redirect](#getgoogleredirect)                     | Redirect to Google for authentication              |
| GET    | [/google/callback](#getgooglecallback)                     | Handle Google authentication callback              |
| POST   | [/login](#postlogin)                                       | Authenticate user and get API token                |
| POST   | [/register](#postregister)                                 | Register a new user                                |
| POST   | [/api/transactions/reverse](#postapitransactionsreverse)   | Reverse a completed transaction                    |
| POST   | [/api/transactions/transfer](#postapitransactionstransfer) | Perform a money transfer between users             |
| POST   | [/api/transactions/deposit](#postapitransactionsdeposit)   | Perform a money deposit for the authenticated user |
| GET    | [/api/users](#getapiusers)                                 | Get a list of all users                            |
| POST   | [/api/users](#postapiusers)                                | Create a new user                                  |
| GET    | [/api/users/{id}](#getapiusersid)                          | Get a single user by ID                            |
| PUT    | [/api/users/{id}](#putapiusersid)                          | Update an existing user                            |
| DELETE | [/api/users/{id}](#deleteapiusersid)                       | Delete a user                                      |
| GET    | [/api/profile](#getapiprofile)                             | Get the authenticated user's profile               |
| POST   | [/password/email](#postpasswordemail)                      | Send password reset link to user's email           |
| POST   | [/password/reset](#postpasswordreset)                      | Reset user password using a token                  |
| GET    | [/email/verify/{id}/{hash}](#getemailverifyidhash)         | Verify user's email address                        |
| POST   | [/email/resend](#postemailresend)                          | Resend email verification link                     |

## Reference Table

| Name                      | Path                                                                                          | Description                                                                                                      |
| ------------------------- | --------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| TransactionReversalStatus | [#/components/schemas/TransactionReversalStatus](#componentsschemastransactionreversalstatus) |                                                                                                                  |
| TransactionStatus         | [#/components/schemas/TransactionStatus](#componentsschemastransactionstatus)                 |                                                                                                                  |
| TransactionType           | [#/components/schemas/TransactionType](#componentsschemastransactiontype)                     |                                                                                                                  |
| UserTypeEnum              | [#/components/schemas/UserTypeEnum](#componentsschemasusertypeenum)                           | Tipos de usuário permitidos no sistema                                                                           |
| MoneyDepositedEvent       | [#/components/schemas/MoneyDepositedEvent](#componentsschemasmoneydepositedevent)             | Evento disparado após um depósito de dinheiro bem-sucedido.                                                      |
| MoneyTransferredEvent     | [#/components/schemas/MoneyTransferredEvent](#componentsschemasmoneytransferredevent)         | Evento disparado após uma transferência de dinheiro bem-sucedida.                                                |
| TransactionDeniedEvent    | [#/components/schemas/TransactionDeniedEvent](#componentsschemastransactiondeniedevent)       | Evento disparado quando uma transação é negada por regras de negócio ou validação.                               |
| TransactionReversedEvent  | [#/components/schemas/TransactionReversedEvent](#componentsschemastransactionreversedevent)   | Evento disparado quando uma transação é revertida com sucesso.                                                   |
| UserGoogleLoginResponse   | [#/components/schemas/UserGoogleLoginResponse](#componentsschemasusergoogleloginresponse)     | User data returned after successful Google login.                                                                |
| TooManyRequestsError      | [#/components/schemas/TooManyRequestsError](#componentsschemastoomanyrequestserror)           | Error response when the rate limit for an endpoint is exceeded.                                                  |
| LoginRequest              | [#/components/schemas/LoginRequest](#componentsschemasloginrequest)                           | Data required for user login.                                                                                    |
| RegisterRequest           | [#/components/schemas/RegisterRequest](#componentsschemasregisterrequest)                     | Data required for user registration.                                                                             |
| DepositRequest            | [#/components/schemas/DepositRequest](#componentsschemasdepositrequest)                       | Data required for a user to make a deposit.                                                                      |
| ReversalRequest           | [#/components/schemas/ReversalRequest](#componentsschemasreversalrequest)                     | Data required to request a transaction reversal.                                                                 |
| TransferRequest           | [#/components/schemas/TransferRequest](#componentsschemastransferrequest)                     | Data required for a user to initiate a money transfer.                                                           |
| UserStoreRequest          | [#/components/schemas/UserStoreRequest](#componentsschemasuserstorerequest)                   | Data required to create a new user by an administrator.                                                          |
| UserUpdateRequest         | [#/components/schemas/UserUpdateRequest](#componentsschemasuserupdaterequest)                 | Data required to update an existing user by an administrator.                                                    |
| Transaction               | [#/components/schemas/Transaction](#componentsschemastransaction)                             | Represents a financial transaction within the system.                                                            |
| TransactionReversal       | [#/components/schemas/TransactionReversal](#componentsschemastransactionreversal)             | Represents a record of a transaction reversal, linking the original transaction to the new reversal transaction. |
| User                      | [#/components/schemas/User](#componentsschemasuser)                                           | Represents a user in the system, either a common user, a shopkeeper, or an administrator.                        |
| AuthenticationError       | [#/components/schemas/AuthenticationError](#componentsschemasauthenticationerror)             |                                                                                                                  |
| TransactionResponse       | [#/components/schemas/TransactionResponse](#componentsschemastransactionresponse)             | Schema representing a transaction, including details about sender and recipient.                                 |
| UnauthorizedReversalError | [#/components/schemas/UnauthorizedReversalError](#componentsschemasunauthorizedreversalerror) | Returned when a user attempts to reverse a transaction they are not authorized to reverse.                       |
| UserResponse              | [#/components/schemas/UserResponse](#componentsschemasuserresponse)                           |                                                                                                                  |
| ValidationError           | [#/components/schemas/ValidationError](#componentsschemasvalidationerror)                     |                                                                                                                  |
| bearerAuth                | [#/components/securitySchemes/bearerAuth](#componentssecurityschemesbearerauth)               |                                                                                                                  |

## Path Details

---

### [GET]/google/redirect

-   Summary  
    Redirect to Google for authentication

#### Responses

-   302 Redirects to Google's authentication page.

---

### [GET]/google/callback

-   Summary  
    Handle Google authentication callback

#### Responses

-   200 Successful Google login, returns authentication token and user data.

`application/json`

```ts
{
  message?: string
  token?: string
  // User data returned after successful Google login.
  user: {
    id?: string
    name?: string
    email?: string
    document?: string
    balance?: number
    // Tipos de usuário permitidos no sistema
    user_type?: enum[COMMON, MERCHANT, ADMIN]
    google_id?: string
    created_at?: string
    updated_at?: string
    email_verified_at?: string
  }
}
```

-   401 Authentication failed with Google.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   500 Server error during user creation.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

---

### [POST]/login

-   Summary  
    Authenticate user and get API token

#### RequestBody

-   application/json

```ts
{
    email: string;
    password: string;
}
```

#### Responses

-   200 Successful login, returns authentication token and user data.

`application/json`

```ts
{
  message?: string
  token?: string
  user: {
    id?: string
    name?: string
    email?: string
    created_at?: string
    updated_at?: string
  }
}
```

-   401 Authentication failed due to invalid credentials.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   422 Validation error.

`application/json`

```ts
{
  message?: string
  errors: {
  }
}
```

-   500 Server error.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

---

### [POST]/register

-   Summary  
    Register a new user

#### RequestBody

-   application/json

```ts
// Data required for user registration.
{
  // The user's full name.
  name: string
  // The user's email address (must be unique).
  email: string
  // The user's CPF (for common users) or CNPJ (for shopkeepers), without formatting.
  document: string
  // The user's password.
  password: string
  // Confirmation of the user's password.
  password_confirmation: string
  // The type of user being registered.
  user_type: enum[common, shopkeeper]
}
```

#### Responses

-   201 User registered successfully.

`application/json`

```ts
{
  message?: string
  user: {
    id?: string
    name?: string
    email?: string
    created_at?: string
    updated_at?: string
  }
}
```

-   422 Validation error.

`application/json`

```ts
{
  message?: string
  errors: {
  }
}
```

-   500 Server error.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

---

### [POST]/api/transactions/reverse

-   Summary  
    Reverse a completed transaction

-   Security  
    bearerAuth

#### RequestBody

-   application/json

```ts
// Data required to request a transaction reversal.
{
    // The UUID of the original transaction to be reversed. This transaction must exist.
    original_transaction_id: string;
    // The reason for the reversal. Must be at least 10 characters long.
    reason: string;
}
```

#### Responses

-   200 Transaction reversed successfully.

`application/json`

```ts
{
  message?: string
  // Schema representing a transaction, including details about sender and recipient.
  reversal_transaction: {
    id?: string
    payer_id?: string
    payee_id?: string
    amount?: string
    type?: string
    status?: string
    created_at?: string
    updated_at?: string
  }
}
```

-   400 Bad request, e.g., invalid transaction ID, already reversed, or business rule violation.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   401 Unauthorized - missing or invalid token.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   403 Forbidden - user does not have permission to reverse this transaction.

`application/json`

```ts
// Returned when a user attempts to reverse a transaction they are not authorized to reverse.
{
  message?: string
  error?: string
}
```

-   422 Validation error - invalid input data.

`application/json`

```ts
{
  message?: string
  errors: {
  }
}
```

-   500 Internal server error.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

---

### [POST]/api/transactions/transfer

-   Summary  
    Perform a money transfer between users

-   Security  
    bearerAuth

#### RequestBody

-   application/json

```ts
// Data required for a user to initiate a money transfer.
{
    // The UUID of the user who will receive the transfer. Must be an existing user and cannot be the payer's own ID.
    payee_id: string;
    // The amount to be transferred. Must be a positive number greater than zero.
    amount: number;
}
```

#### Responses

-   200 Transfer performed successfully or denied with a reason.

`application/json`

```ts
{
  message?: string
  // Schema representing a transaction, including details about sender and recipient.
  transaction: {
    id?: string
    payer_id?: string
    payee_id?: string
    amount?: string
    type?: string
    status?: string
    created_at?: string
    updated_at?: string
  }
  // Reason for denial if status is DENIED.
  reason?: string
}
```

-   401 Unauthorized - missing or invalid token.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   403 Forbidden - user type not allowed to transfer.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   404 Payee not found.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   422 Validation error - invalid input data.

`application/json`

```ts
{
  message?: string
  errors: {
  }
}
```

-   500 Internal server error.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

---

### [POST]/api/transactions/deposit

-   Summary  
    Perform a money deposit for the authenticated user

-   Security  
    bearerAuth

#### RequestBody

-   application/json

```ts
// Data required for a user to make a deposit.
{
    // The amount to be deposited. Must be greater than zero.
    amount: number;
}
```

#### Responses

-   200 Deposit performed successfully.

`application/json`

```ts
{
  message?: string
  // Schema representing a transaction, including details about sender and recipient.
  transaction: {
    id?: string
    payer_id?: string
    payee_id?: string
    amount?: string
    type?: string
    status?: string
    created_at?: string
    updated_at?: string
  }
}
```

-   401 Unauthorized - missing or invalid token.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   422 Validation error - invalid input data.

`application/json`

```ts
{
  message?: string
  errors: {
  }
}
```

-   500 Internal server error.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

---

### [GET]/api/users

-   Summary  
    Get a list of all users

-   Description  
    Display a listing of the users.

-   Security  
    bearerAuth

#### Parameters(Query)

```ts
per_page?: integer //default: 10
```

#### Responses

-   200 Successful operation

`application/json`

```ts
{
  data: {
    id?: string
    name?: string
    email?: string
    created_at?: string
    updated_at?: string
  }[]
  links: {
  }
  meta: {
  }
}
```

-   401 Unauthorized - missing or invalid token.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   403 Forbidden - user does not have admin access.

`application/json`

```ts
{
  message?: string
}
```

-   500 Server error.

`application/json`

```ts
{
  message?: string
}
```

---

### [POST]/api/users

-   Summary  
    Create a new user

-   Description  
    Store a newly created user in storage.

-   Security  
    bearerAuth

#### RequestBody

-   application/json

```ts
// Data required to create a new user by an administrator.
{
  // The full name of the user.
  name: string
  // The unique email address for the user.
  email: string
  // The unique document number (CPF or CNPJ) for the user. Only digits.
  document: string
  // The user's password. Must be at least 8 characters and confirmed.
  password: string
  // Confirmation of the user's password. Must match 'password'.
  password_confirmation: string
  // The type of user being created.
  user_type: enum[common, shopkeeper, admin]
}
```

#### Responses

-   201 User created successfully.

`application/json`

```ts
{
  message?: string
  user: {
    id?: string
    name?: string
    email?: string
    created_at?: string
    updated_at?: string
  }
}
```

-   401 Unauthorized - missing or invalid token.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   403 Forbidden - user does not have admin access.

`application/json`

```ts
{
  message?: string
}
```

-   422 Validation failed.

`application/json`

```ts
{
  message?: string
  errors: {
  }
}
```

-   500 Server error.

`application/json`

```ts
{
  message?: string
}
```

---

### [GET]/api/users/{id}

-   Summary  
    Get a single user by ID

-   Description  
    Display the specified user.

-   Security  
    bearerAuth

#### Responses

-   200 Successful operation

`application/json`

```ts
{
  id?: string
  name?: string
  email?: string
  created_at?: string
  updated_at?: string
}
```

-   401 Unauthorized - missing or invalid token.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   403 Forbidden - user does not have access to this user's data.

`application/json`

```ts
{
  message?: string
}
```

-   404 User not found.

`application/json`

```ts
{
  message?: string
}
```

-   500 Server error.

`application/json`

```ts
{
  message?: string
}
```

---

### [PUT]/api/users/{id}

-   Summary  
    Update an existing user

-   Description  
    Update the specified user in storage.

-   Security  
    bearerAuth

#### RequestBody

-   application/json

```ts
// Data required to update an existing user by an administrator.
{
  // The updated full name of the user.
  name: string
  // The updated unique email address for the user.
  email: string
  // The updated unique document number (CPF or CNPJ) for the user. Only digits.
  document: string
  // The new password for the user. Optional. Must be at least 8 characters and confirmed if provided.
  password?: string
  // Confirmation of the new password. Required if 'password' is provided and must match 'password'.
  password_confirmation?: string
  // The updated type of user.
  user_type: enum[common, shopkeeper, admin]
}
```

#### Responses

-   200 User updated successfully.

`application/json`

```ts
{
  message?: string
  user: {
    id?: string
    name?: string
    email?: string
    created_at?: string
    updated_at?: string
  }
}
```

-   401 Unauthorized - missing or invalid token.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   403 Forbidden - user does not have access to update this user's data.

`application/json`

```ts
{
  message?: string
}
```

-   404 User not found.

`application/json`

```ts
{
  message?: string
}
```

-   422 Validation failed.

`application/json`

```ts
{
  message?: string
  errors: {
  }
}
```

-   500 Server error.

`application/json`

```ts
{
  message?: string
}
```

---

### [DELETE]/api/users/{id}

-   Summary  
    Delete a user

-   Description  
    Remove the specified user from storage.

-   Security  
    bearerAuth

#### Responses

-   204 User deleted successfully.

-   401 Unauthorized - missing or invalid token.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   403 Forbidden - user does not have admin access or cannot delete themselves.

`application/json`

```ts
{
  message?: string
}
```

-   404 User not found.

`application/json`

```ts
{
  message?: string
}
```

-   500 Server error.

`application/json`

```ts
{
  message?: string
}
```

---

### [GET]/api/profile

-   Summary  
    Get the authenticated user's profile

-   Description  
    Display the authenticated user's profile.

-   Security  
    bearerAuth

#### Responses

-   200 Successful operation

`application/json`

```ts
{
  id?: string
  name?: string
  email?: string
  created_at?: string
  updated_at?: string
}
```

-   401 Unauthorized - missing or invalid token.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   500 Server error.

`application/json`

```ts
{
  message?: string
}
```

---

### [POST]/password/email

-   Summary  
    Send password reset link to user's email

#### RequestBody

-   application/json

```ts
{
    // The user's email address.
    email: string;
}
```

#### Responses

-   200 Password reset link sent successfully.

`application/json`

```ts
{
  message?: string
}
```

-   422 Validation error (e.g., email not found, invalid email format).

`application/json`

```ts
{
  message?: string
  errors: {
  }
}
```

-   500 Internal server error.

`application/json`

```ts
{
  message?: string
}
```

---

### [POST]/password/reset

-   Summary  
    Reset user password using a token

#### RequestBody

-   application/json

```ts
{
    // The password reset token received via email.
    token: string;
    // The user's email address.
    email: string;
    // The new password for the user.
    password: string;
    // Confirmation of the new password.
    password_confirmation: string;
}
```

#### Responses

-   302 Redirect to /home on successful password reset.

-   422 Validation error (e.g., invalid token, email not found, passwords don't match, or weak password).

`application/json`

```ts
{
  message?: string
  errors: {
  }
}
```

-   500 Internal server error during password reset.

---

### [GET]/email/verify/{id}/{hash}

-   Summary  
    Verify user's email address

-   Description  
    Verifies the user's email address using the provided ID and hash from the verification link. This is typically a web route.

#### Responses

-   302 Redirect to /home on successful email verification.

-   401 Unauthorized - if the user is not authenticated.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   403 Forbidden - if the link is invalid or expired (e.g., 'signed' middleware failure).

`application/json`

```ts
{
  message?: string
}
```

---

### [POST]/email/resend

-   Summary  
    Resend email verification link

-   Description  
    Sends a new email verification link to the authenticated user's email address.

-   Security  
    bearerAuth

#### Responses

-   200 Verification email resent successfully.

`application/json`

```ts
{
  message?: string
}
```

-   401 Unauthorized - if the user is not authenticated.

`application/json`

```ts
{
  message?: string
  error?: string
}
```

-   429 Too Many Requests - if the user tries to resend too frequently (throttled).

`application/json`

```ts
{
  message?: string
}
```

## References

### #/components/schemas/TransactionReversalStatus

```ts
{
  "type": "string",
  "enum": [
    "PENDING",
    "COMPLETED",
    "FAILED",
    "REVERSED",
    "DENIED"
  ],
  "example": "PENDING"
}
```

### #/components/schemas/TransactionStatus

```ts
{
  "type": "string",
  "enum": [
    "PENDING",
    "COMPLETED",
    "FAILED",
    "REVERSED",
    "DENIED"
  ],
  "example": "PENDING"
}
```

### #/components/schemas/TransactionType

```ts
{
  "type": "string",
  "enum": [
    "TRANSFER",
    "DEPOSIT",
    "REVERSAL"
  ],
  "example": "TRANSFER"
}
```

### #/components/schemas/UserTypeEnum

```ts
{
  "title": "UserTypeEnum",
  "description": "Tipos de usuário permitidos no sistema",
  "type": "string",
  "enum": [
    "COMMON",
    "MERCHANT",
    "ADMIN"
  ],
  "example": "COMMON"
}
```

### #/components/schemas/MoneyDepositedEvent

```ts
// Evento disparado após um depósito de dinheiro bem-sucedido.
{
  // Represents a financial transaction within the system.
  transaction: {
    // The unique identifier of the transaction.
    id?: string
    // The ID of the user who initiated the transaction (payer). Null for deposits.
    payer_id?: string
    // The ID of the user who received the transaction (payee).
    payee_id?: string
    // The amount of money involved in the transaction.
    amount?: number
    // The current status of the transaction.
    status?: enum[pending, completed, denied, reversed]
    // The type of the transaction.
    type?: enum[transfer, deposit, reversal]
    // The ID of the original transaction that this transaction reversed (if type is reversal).
    reverted_from?: string
    // The reason for the transaction status (e.g., denial reason, reversal reason).
    reason?: string
    // Timestamp when the transaction was created.
    created_at?: string
    // Timestamp when the transaction was last updated.
    updated_at?: string
  }
  // Represents a user in the system, either a common user, a shopkeeper, or an administrator.
  user: {
    // The unique identifier of the user.
    id?: string
    // The full name of the user.
    name?: string
    // The unique email address of the user.
    email?: string
    // The unique CPF (for common users) or CNPJ (for shopkeepers), containing only digits.
    document?: string
    // The current monetary balance of the user, with 2 decimal places.
    balance?: number
    // The type of user.
    user_type?: enum[common, shopkeeper, admin]
    // The Google ID if the user registered via Google OAuth.
    google_id?: string
    // Timestamp when the user account was created.
    created_at?: string
    // Timestamp when the user account was last updated.
    updated_at?: string
  }
  // O valor depositado.
  amount?: number
}
```

### #/components/schemas/MoneyTransferredEvent

```ts
// Evento disparado após uma transferência de dinheiro bem-sucedida.
{
  // Represents a financial transaction within the system.
  transaction: {
    // The unique identifier of the transaction.
    id?: string
    // The ID of the user who initiated the transaction (payer). Null for deposits.
    payer_id?: string
    // The ID of the user who received the transaction (payee).
    payee_id?: string
    // The amount of money involved in the transaction.
    amount?: number
    // The current status of the transaction.
    status?: enum[pending, completed, denied, reversed]
    // The type of the transaction.
    type?: enum[transfer, deposit, reversal]
    // The ID of the original transaction that this transaction reversed (if type is reversal).
    reverted_from?: string
    // The reason for the transaction status (e.g., denial reason, reversal reason).
    reason?: string
    // Timestamp when the transaction was created.
    created_at?: string
    // Timestamp when the transaction was last updated.
    updated_at?: string
  }
  // Represents a user in the system, either a common user, a shopkeeper, or an administrator.
  payer: {
    // The unique identifier of the user.
    id?: string
    // The full name of the user.
    name?: string
    // The unique email address of the user.
    email?: string
    // The unique CPF (for common users) or CNPJ (for shopkeepers), containing only digits.
    document?: string
    // The current monetary balance of the user, with 2 decimal places.
    balance?: number
    // The type of user.
    user_type?: enum[common, shopkeeper, admin]
    // The Google ID if the user registered via Google OAuth.
    google_id?: string
    // Timestamp when the user account was created.
    created_at?: string
    // Timestamp when the user account was last updated.
    updated_at?: string
  }
  payee:#/components/schemas/User
  // O valor transferido.
  amount?: number
}
```

### #/components/schemas/TransactionDeniedEvent

```ts
// Evento disparado quando uma transação é negada por regras de negócio ou validação.
{
  // Represents a financial transaction within the system.
  transaction: {
    // The unique identifier of the transaction.
    id?: string
    // The ID of the user who initiated the transaction (payer). Null for deposits.
    payer_id?: string
    // The ID of the user who received the transaction (payee).
    payee_id?: string
    // The amount of money involved in the transaction.
    amount?: number
    // The current status of the transaction.
    status?: enum[pending, completed, denied, reversed]
    // The type of the transaction.
    type?: enum[transfer, deposit, reversal]
    // The ID of the original transaction that this transaction reversed (if type is reversal).
    reverted_from?: string
    // The reason for the transaction status (e.g., denial reason, reversal reason).
    reason?: string
    // Timestamp when the transaction was created.
    created_at?: string
    // Timestamp when the transaction was last updated.
    updated_at?: string
  }
  // Represents a user in the system, either a common user, a shopkeeper, or an administrator.
  payer: {
    // The unique identifier of the user.
    id?: string
    // The full name of the user.
    name?: string
    // The unique email address of the user.
    email?: string
    // The unique CPF (for common users) or CNPJ (for shopkeepers), containing only digits.
    document?: string
    // The current monetary balance of the user, with 2 decimal places.
    balance?: number
    // The type of user.
    user_type?: enum[common, shopkeeper, admin]
    // The Google ID if the user registered via Google OAuth.
    google_id?: string
    // Timestamp when the user account was created.
    created_at?: string
    // Timestamp when the user account was last updated.
    updated_at?: string
  }
  payee:#/components/schemas/User
  // O valor que foi tentado transferir.
  amount?: number
  // O motivo da negação da transação.
  reason?: string
}
```

### #/components/schemas/TransactionReversedEvent

```ts
// Evento disparado quando uma transação é revertida com sucesso.
{
  // Represents a financial transaction within the system.
  originalTransaction: {
    // The unique identifier of the transaction.
    id?: string
    // The ID of the user who initiated the transaction (payer). Null for deposits.
    payer_id?: string
    // The ID of the user who received the transaction (payee).
    payee_id?: string
    // The amount of money involved in the transaction.
    amount?: number
    // The current status of the transaction.
    status?: enum[pending, completed, denied, reversed]
    // The type of the transaction.
    type?: enum[transfer, deposit, reversal]
    // The ID of the original transaction that this transaction reversed (if type is reversal).
    reverted_from?: string
    // The reason for the transaction status (e.g., denial reason, reversal reason).
    reason?: string
    // Timestamp when the transaction was created.
    created_at?: string
    // Timestamp when the transaction was last updated.
    updated_at?: string
  }
  reversalTransaction:#/components/schemas/Transaction
  // Represents a record of a transaction reversal, linking the original transaction to the new reversal transaction.
  transactionReversalRecord: {
    // The unique identifier of the transaction reversal record.
    id?: string
    // The ID of the original transaction that was requested to be reversed.
    original_transaction_id?: string
    // The ID of the new transaction of type 'reversal' generated in the system. Null if the reversal failed or is pending.
    reversal_transaction_id?: string
    // The ID of the user (e.g., administrator) who initiated or approved this reversal request.
    reversed_by_user_id?: string
    // The reason provided for the transaction reversal.
    reason?: string
    // The current status of the reversal request process.
    status?: enum[pending, completed, failed, rejected]
    // Timestamp when the reversal record was created.
    created_at?: string
    // Timestamp when the reversal record was last updated.
    updated_at?: string
  }
  // O usuário pagador da transação original (pode ser null para depósitos).
  payer?: #/components/schemas/User
  // Represents a user in the system, either a common user, a shopkeeper, or an administrator.
  payee: {
    // The unique identifier of the user.
    id?: string
    // The full name of the user.
    name?: string
    // The unique email address of the user.
    email?: string
    // The unique CPF (for common users) or CNPJ (for shopkeepers), containing only digits.
    document?: string
    // The current monetary balance of the user, with 2 decimal places.
    balance?: number
    // The type of user.
    user_type?: enum[common, shopkeeper, admin]
    // The Google ID if the user registered via Google OAuth.
    google_id?: string
    // Timestamp when the user account was created.
    created_at?: string
    // Timestamp when the user account was last updated.
    updated_at?: string
  }
  // O valor que foi revertido.
  amount?: number
  // O motivo da reversão.
  reason?: string
}
```

### #/components/schemas/UserGoogleLoginResponse

```ts
// User data returned after successful Google login.
{
  id?: string
  name?: string
  email?: string
  document?: string
  balance?: number
  // Tipos de usuário permitidos no sistema
  user_type?: enum[COMMON, MERCHANT, ADMIN]
  google_id?: string
  created_at?: string
  updated_at?: string
  email_verified_at?: string
}
```

### #/components/schemas/TooManyRequestsError

```ts
// Error response when the rate limit for an endpoint is exceeded.
{
  message?: string
  exception?: string
}
```

### #/components/schemas/LoginRequest

```ts
// Data required for user login.
{
    // The user's email address.
    email: string;
    // The user's password.
    password: string;
}
```

### #/components/schemas/RegisterRequest

```ts
// Data required for user registration.
{
  // The user's full name.
  name: string
  // The user's email address (must be unique).
  email: string
  // The user's CPF (for common users) or CNPJ (for shopkeepers), without formatting.
  document: string
  // The user's password.
  password: string
  // Confirmation of the user's password.
  password_confirmation: string
  // The type of user being registered.
  user_type: enum[common, shopkeeper]
}
```

### #/components/schemas/DepositRequest

```ts
// Data required for a user to make a deposit.
{
    // The amount to be deposited. Must be greater than zero.
    amount: number;
}
```

### #/components/schemas/ReversalRequest

```ts
// Data required to request a transaction reversal.
{
    // The UUID of the original transaction to be reversed. This transaction must exist.
    original_transaction_id: string;
    // The reason for the reversal. Must be at least 10 characters long.
    reason: string;
}
```

### #/components/schemas/TransferRequest

```ts
// Data required for a user to initiate a money transfer.
{
    // The UUID of the user who will receive the transfer. Must be an existing user and cannot be the payer's own ID.
    payee_id: string;
    // The amount to be transferred. Must be a positive number greater than zero.
    amount: number;
}
```

### #/components/schemas/UserStoreRequest

```ts
// Data required to create a new user by an administrator.
{
  // The full name of the user.
  name: string
  // The unique email address for the user.
  email: string
  // The unique document number (CPF or CNPJ) for the user. Only digits.
  document: string
  // The user's password. Must be at least 8 characters and confirmed.
  password: string
  // Confirmation of the user's password. Must match 'password'.
  password_confirmation: string
  // The type of user being created.
  user_type: enum[common, shopkeeper, admin]
}
```

### #/components/schemas/UserUpdateRequest

```ts
// Data required to update an existing user by an administrator.
{
  // The updated full name of the user.
  name: string
  // The updated unique email address for the user.
  email: string
  // The updated unique document number (CPF or CNPJ) for the user. Only digits.
  document: string
  // The new password for the user. Optional. Must be at least 8 characters and confirmed if provided.
  password?: string
  // Confirmation of the new password. Required if 'password' is provided and must match 'password'.
  password_confirmation?: string
  // The updated type of user.
  user_type: enum[common, shopkeeper, admin]
}
```

### #/components/schemas/Transaction

```ts
// Represents a financial transaction within the system.
{
  // The unique identifier of the transaction.
  id?: string
  // The ID of the user who initiated the transaction (payer). Null for deposits.
  payer_id?: string
  // The ID of the user who received the transaction (payee).
  payee_id?: string
  // The amount of money involved in the transaction.
  amount?: number
  // The current status of the transaction.
  status?: enum[pending, completed, denied, reversed]
  // The type of the transaction.
  type?: enum[transfer, deposit, reversal]
  // The ID of the original transaction that this transaction reversed (if type is reversal).
  reverted_from?: string
  // The reason for the transaction status (e.g., denial reason, reversal reason).
  reason?: string
  // Timestamp when the transaction was created.
  created_at?: string
  // Timestamp when the transaction was last updated.
  updated_at?: string
}
```

### #/components/schemas/TransactionReversal

```ts
// Represents a record of a transaction reversal, linking the original transaction to the new reversal transaction.
{
  // The unique identifier of the transaction reversal record.
  id?: string
  // The ID of the original transaction that was requested to be reversed.
  original_transaction_id?: string
  // The ID of the new transaction of type 'reversal' generated in the system. Null if the reversal failed or is pending.
  reversal_transaction_id?: string
  // The ID of the user (e.g., administrator) who initiated or approved this reversal request.
  reversed_by_user_id?: string
  // The reason provided for the transaction reversal.
  reason?: string
  // The current status of the reversal request process.
  status?: enum[pending, completed, failed, rejected]
  // Timestamp when the reversal record was created.
  created_at?: string
  // Timestamp when the reversal record was last updated.
  updated_at?: string
}
```

### #/components/schemas/User

```ts
// Represents a user in the system, either a common user, a shopkeeper, or an administrator.
{
  // The unique identifier of the user.
  id?: string
  // The full name of the user.
  name?: string
  // The unique email address of the user.
  email?: string
  // The unique CPF (for common users) or CNPJ (for shopkeepers), containing only digits.
  document?: string
  // The current monetary balance of the user, with 2 decimal places.
  balance?: number
  // The type of user.
  user_type?: enum[common, shopkeeper, admin]
  // The Google ID if the user registered via Google OAuth.
  google_id?: string
  // Timestamp when the user account was created.
  created_at?: string
  // Timestamp when the user account was last updated.
  updated_at?: string
}
```

### #/components/schemas/AuthenticationError

```ts
{
  message?: string
  error?: string
}
```

### #/components/schemas/TransactionResponse

```ts
// Schema representing a transaction, including details about sender and recipient.
{
  id?: string
  payer_id?: string
  payee_id?: string
  amount?: string
  type?: string
  status?: string
  created_at?: string
  updated_at?: string
}
```

### #/components/schemas/UnauthorizedReversalError

```ts
// Returned when a user attempts to reverse a transaction they are not authorized to reverse.
{
  message?: string
  error?: string
}
```

### #/components/schemas/UserResponse

```ts
{
  id?: string
  name?: string
  email?: string
  created_at?: string
  updated_at?: string
}
```

### #/components/schemas/ValidationError

```ts
{
  message?: string
  errors: {
  }
}
```

### #/components/securitySchemes/bearerAuth

```ts
{
  "type": "http",
  "bearerFormat": "JWT",
  "scheme": "bearer"
}
```
