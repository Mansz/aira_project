# AIRA API Documentation

## Base URL
- Development: `http://localhost:8085/api/v1`
- Production: `https://api.aira.com/api/v1` (replace with actual production URL)

## Authentication

All authenticated endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer <token>
```

### Android Authentication Endpoints

#### Regular Login
- `POST /auth/login`
  - Body: 
    ```json
    {
      "email": "string",
      "password": "string",
      "device_name": "string"
    }
    ```
  - Response: 
    ```json
    {
      "success": true,
      "data": {
        "token": "string",
        "user": {}
      }
    }
    ```

#### Google Sign In
- `POST /auth/google`
  - Body:
    ```json
    {
      "google_token": "string",
      "device_name": "string"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "token": "string",
        "user": {},
        "requires_whatsapp": boolean
      }
    }
    ```

#### Send OTP
- `POST /auth/send-otp`
  - Body:
    ```json
    {
      "whatsapp": "string"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "OTP sent successfully"
    }
    ```

#### Verify OTP
- `POST /auth/verify-otp`
  - Body:
    ```json
    {
      "whatsapp": "string",
      "otp": "string"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "OTP verified successfully"
    }
    ```

#### Register
- `POST /auth/register`
  - Body:
    ```json
    {
      "name": "string",
      "email": "string",
      "whatsapp": "string",
      "password": "string",
      "password_confirmation": "string"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "token": "string",
        "user": {}
      }
    }
    ```

### User Profile Endpoints

#### Get Profile
- `GET /user/profile`
  - Response: User profile data

#### Update Profile
- `PUT /user/profile`
  - Body: Profile update data
  - Response: Updated profile

#### Logout
- `POST /auth/logout`
  - Response: Success message

### Products

#### List Products
- `GET /products`
  - Query params: `page`, `per_page`, `category_id`, `search`
  - Response: Paginated list of products

#### Get Product
- `GET /products/{id}`
  - Response: Single product details

#### List Categories
- `GET /products/categories`
  - Response: List of product categories

### Live Streaming

#### List Active Streams
- `GET /streams/active`
  - Response: List of active live streams

#### Get Stream Details
- `GET /streams/{streamId}`
  - Response: Stream details with products

#### Join Stream
- `POST /streams/{streamId}/join`
  - Response: Stream token and details

#### Leave Stream
- `POST /streams/{streamId}/leave`
  - Response: Success message

#### Get Stream Comments
- `GET /streams/{streamId}/comments`
  - Response: List of stream comments

#### Send Comment
- `POST /streams/{streamId}/comments`
  - Body: `{ "content": "string" }`
  - Response: Created comment

### Cart Management

#### Get Cart
- `GET /cart`
  - Response: User's cart items

#### Add to Cart
- `POST /cart/add`
  - Body: `{ "product_id": number, "quantity": number }`
  - Response: Updated cart

#### Update Cart Item
- `PUT /cart/update/{cartItem}`
  - Body: `{ "quantity": number }`
  - Response: Updated cart item

#### Remove from Cart
- `DELETE /cart/remove/{cartItem}`
  - Response: Success message

### Orders

#### List Orders
- `GET /orders`
  - Query params: `page`, `per_page`, `status`
  - Response: Paginated list of user's orders

#### Create Order
- `POST /orders`
  - Body: Order details with items
  - Response: Created order

#### Get Order Details
- `GET /orders/{order}`
  - Response: Order details with items

#### Cancel Order
- `POST /orders/{order}/cancel`
  - Response: Updated order

#### Upload Payment Proof
- `POST /orders/{order}/payment-proof`
  - Body: `form-data` with `proof` file
  - Response: Payment proof details

## Error Responses
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error description"]
  }
}
```

## Status Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 429: Too Many Requests
- 500: Server Error

## Rate Limiting
- Public API: 60 requests per minute
- Authenticated API: 120 requests per minute
- Admin API: 300 requests per minute
