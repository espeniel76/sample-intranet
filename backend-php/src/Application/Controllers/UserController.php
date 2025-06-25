<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Services\UserService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 사용자 컨트롤러
 */
class UserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * 회원가입
     */
    public function register(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            // 필수 필드 검증
            $this->validateRegisterData($data);
            
            $user = $this->userService->createUser($data);
            
            $responseData = $user->jsonSerialize();
            $response->getBody()->write(json_encode($responseData));
            return $response->withStatus(201);
            
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($response, '회원가입 실패', $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse($response, '서버 오류', $e->getMessage(), 500);
        }
    }

    /**
     * 로그인
     */
    public function login(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (empty($data['email']) || empty($data['password'])) {
                return $this->errorResponse($response, '로그인 실패', '이메일과 비밀번호를 입력해주세요', 400);
            }
            
            $authData = $this->userService->authenticateUser($data['email'], $data['password']);
            
            if (!$authData) {
                return $this->errorResponse($response, '로그인 실패', '이메일 또는 비밀번호가 올바르지 않습니다', 401);
            }
            
            $response->getBody()->write(json_encode($authData));
            return $response;
            
        } catch (\Exception $e) {
            return $this->errorResponse($response, '서버 오류', $e->getMessage(), 500);
        }
    }

    /**
     * 사용자 목록 조회
     */
    public function getUsers(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $limit = (int) ($params['limit'] ?? 100);
            $skip = (int) ($params['skip'] ?? 0);
            
            $users = $this->userService->getAllUsers($limit, $skip);
            $responseData = array_map(fn($user) => $user->jsonSerialize(), $users);
            
            $response->getBody()->write(json_encode($responseData));
            return $response;
            
        } catch (\Exception $e) {
            return $this->errorResponse($response, '사용자 목록 조회 실패', $e->getMessage(), 500);
        }
    }

    /**
     * 특정 사용자 조회
     */
    public function getUser(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                return $this->errorResponse($response, '사용자를 찾을 수 없습니다', "ID {$id}에 해당하는 사용자가 없습니다", 404);
            }
            
            $responseData = $user->jsonSerialize();
            $response->getBody()->write(json_encode($responseData));
            return $response;
            
        } catch (\Exception $e) {
            return $this->errorResponse($response, '사용자 조회 실패', $e->getMessage(), 500);
        }
    }

    /**
     * 사용자 정보 수정
     */
    public function updateUser(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $request->getParsedBody();
            
            $user = $this->userService->updateUser($id, $data);
            
            $responseData = $user->jsonSerialize();
            $response->getBody()->write(json_encode($responseData));
            return $response;
            
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($response, '사용자 정보 수정 실패', $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse($response, '서버 오류', $e->getMessage(), 500);
        }
    }

    /**
     * 사용자 삭제 (관리자 전용)
     */
    public function deleteUser(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $this->userService->deleteUser($id);
            
            $responseData = [
                'success' => true,
                'message' => '사용자가 삭제되었습니다'
            ];
            
            $response->getBody()->write(json_encode($responseData));
            return $response;
            
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($response, '사용자 삭제 실패', $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse($response, '서버 오류', $e->getMessage(), 500);
        }
    }

    /**
     * 사용자 검색
     */
    public function searchUsers(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $name = $params['name'] ?? '';
            
            if (empty($name)) {
                return $this->errorResponse($response, '검색어 필요', 'name 파라미터를 입력해주세요', 400);
            }
            
            $users = $this->userService->searchUsersByName($name);
            $responseData = array_map(fn($user) => $user->jsonSerialize(), $users);
            
            $response->getBody()->write(json_encode($responseData));
            return $response;
            
        } catch (\Exception $e) {
            return $this->errorResponse($response, '사용자 검색 실패', $e->getMessage(), 500);
        }
    }

    /**
     * 회원가입 데이터 검증
     */
    private function validateRegisterData(array $data): void
    {
        if (empty($data['email'])) {
            throw new \InvalidArgumentException('이메일은 필수입니다');
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('올바른 이메일 형식이 아닙니다');
        }
        
        if (empty($data['password'])) {
            throw new \InvalidArgumentException('비밀번호는 필수입니다');
        }
        
        if (strlen($data['password']) < 6) {
            throw new \InvalidArgumentException('비밀번호는 최소 6자 이상이어야 합니다');
        }
        
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('이름은 필수입니다');
        }
    }

    /**
     * 에러 응답 생성
     */
    private function errorResponse(Response $response, string $error, string $detail, int $status): Response
    {
        $errorData = [
            'error' => $error,
            'detail' => $detail
        ];
        
        $response->getBody()->write(json_encode($errorData));
        return $response->withStatus($status);
    }
} 