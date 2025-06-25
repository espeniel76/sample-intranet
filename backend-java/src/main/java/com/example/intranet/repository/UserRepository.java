package com.example.intranet.repository;

import com.example.intranet.entity.User;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

/**
 * 사용자 레포지토리
 * Spring Data JPA를 사용한 데이터 액세스 계층
 */
@Repository
public interface UserRepository extends JpaRepository<User, Long> {

    /**
     * 이메일로 사용자 찾기
     */
    Optional<User> findByEmail(String email);

    /**
     * 이메일 존재 여부 확인
     */
    boolean existsByEmail(String email);

    /**
     * 활성 사용자만 조회
     */
    @Query("SELECT u FROM User u WHERE u.isActive = true")
    List<User> findAllActiveUsers();

    /**
     * 이름으로 사용자 검색 (부분 일치)
     */
    @Query("SELECT u FROM User u WHERE u.name LIKE %:name% AND u.isActive = true")
    List<User> findByNameContaining(@Param("name") String name);

    /**
     * 이메일과 활성 상태로 사용자 찾기
     */
    Optional<User> findByEmailAndIsActive(String email, Boolean isActive);
} 