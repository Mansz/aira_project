#!/bin/bash

# Warna untuk output
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}Starting CORS & Sanctum Endpoint Testing...${NC}\n"

# Base URL
BASE_URL="http://localhost:8000"
FRONTEND_URL="http://localhost:3000"

# Function untuk print hasil
print_result() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓ $2${NC}"
    else
        echo -e "${RED}✗ $2${NC}"
        echo -e "${RED}Error: $3${NC}"
    fi
    echo ""
}

echo -e "${BLUE}1. Testing CSRF Cookie Endpoint${NC}"
# Get CSRF Cookie dengan Origin header
CSRF_RESPONSE=$(curl -s -I -X GET \
    -H "Origin: $FRONTEND_URL" \
    -H "Accept: application/json" \
    -c cookies.txt \
    "$BASE_URL/sanctum/csrf-cookie")

# Check status code
STATUS=$(echo "$CSRF_RESPONSE" | grep "HTTP" | awk '{print $2}')
if [ "$STATUS" = "204" ]; then
    print_result 0 "CSRF Cookie Endpoint: Success (Status 204)"
    # Extract XSRF-TOKEN from cookies.txt
    XSRF_TOKEN=$(grep "XSRF-TOKEN" cookies.txt | awk '{print $7}')
    echo "XSRF-TOKEN: $XSRF_TOKEN"
else
    print_result 1 "CSRF Cookie Endpoint" "Expected status 204, got $STATUS"
fi

echo -e "${BLUE}2. Testing Login Endpoint${NC}"
# Try login with CSRF token
LOGIN_RESPONSE=$(curl -s -X POST \
    -H "Origin: $FRONTEND_URL" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
    -b cookies.txt \
    -c cookies.txt \
    -d '{"email":"admin@example.com","password":"password"}' \
    "$BASE_URL/login")

# Check if login response contains error
if echo "$LOGIN_RESPONSE" | grep -q "error"; then
    print_result 1 "Login Endpoint" "$LOGIN_RESPONSE"
else
    print_result 0 "Login Endpoint: Success"
    echo "Response: $LOGIN_RESPONSE"
fi

echo -e "${BLUE}3. Testing Protected Route (/api/user)${NC}"
# Try accessing protected route
USER_RESPONSE=$(curl -s -X GET \
    -H "Origin: $FRONTEND_URL" \
    -H "Accept: application/json" \
    -b cookies.txt \
    "$BASE_URL/api/user")

# Check if user response contains error
if echo "$USER_RESPONSE" | grep -q "error"; then
    print_result 1 "Protected Route" "$USER_RESPONSE"
else
    print_result 0 "Protected Route: Success"
    echo "Response: $USER_RESPONSE"
fi

echo -e "${BLUE}4. Testing Logout${NC}"
# Try logout
LOGOUT_RESPONSE=$(curl -s -X POST \
    -H "Origin: $FRONTEND_URL" \
    -H "Accept: application/json" \
    -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
    -b cookies.txt \
    "$BASE_URL/logout")

# Check logout response
if [ -z "$LOGOUT_RESPONSE" ]; then
    print_result 0 "Logout Endpoint: Success"
else
    print_result 1 "Logout Endpoint" "$LOGOUT_RESPONSE"
fi

# Cleanup
rm -f cookies.txt

echo -e "${BLUE}Testing Complete!${NC}"
