#!/bin/bash

# Sample Intranet Backend API 테스트 스크립트

BASE_URL="http://localhost:8080"

echo "=== Sample Intranet Backend API 테스트 ==="
echo ""

# 1. 헬스체크
echo "1. 헬스체크 테스트"
curl -s "$BASE_URL/health" | jq .
echo ""

# 2. 회원가입 테스트
echo "2. 회원가입 테스트"
REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/api/v1/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123",
    "name": "테스트 사용자",
    "role": "user"
  }')
echo $REGISTER_RESPONSE | jq .
echo ""

# 3. 로그인 테스트
echo "3. 로그인 테스트"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }')
echo $LOGIN_RESPONSE | jq .

# JWT 토큰 추출
TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.token // empty')
echo ""

if [ -z "$TOKEN" ] || [ "$TOKEN" = "null" ]; then
  echo "로그인 실패: 토큰을 가져올 수 없습니다."
  exit 1
fi

echo "JWT 토큰: $TOKEN"
echo ""

# 4. 사용자 목록 조회 테스트
echo "4. 사용자 목록 조회 테스트"
curl -s -X GET "$BASE_URL/api/v1/users" \
  -H "Authorization: Bearer $TOKEN" | jq .
echo ""

# 5. 관리자 계정 생성 테스트
echo "5. 관리자 계정 생성 테스트"
ADMIN_REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/api/v1/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin123",
    "name": "관리자",
    "role": "admin"
  }')
echo $ADMIN_REGISTER_RESPONSE | jq .
echo ""

# 6. 관리자 로그인 테스트
echo "6. 관리자 로그인 테스트"
ADMIN_LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin123"
  }')
echo $ADMIN_LOGIN_RESPONSE | jq .

# 관리자 토큰 추출
ADMIN_TOKEN=$(echo $ADMIN_LOGIN_RESPONSE | jq -r '.token // empty')
echo ""

if [ -z "$ADMIN_TOKEN" ] || [ "$ADMIN_TOKEN" = "null" ]; then
  echo "관리자 로그인 실패: 토큰을 가져올 수 없습니다."
  exit 1
fi

echo "관리자 JWT 토큰: $ADMIN_TOKEN"
echo ""

# 7. 사용자 정보 수정 테스트
echo "7. 사용자 정보 수정 테스트"
curl -s -X PUT "$BASE_URL/api/v1/users/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "수정된 사용자",
    "email": "updated@example.com"
  }' | jq .
echo ""

echo "=== API 테스트 완료 ===" 