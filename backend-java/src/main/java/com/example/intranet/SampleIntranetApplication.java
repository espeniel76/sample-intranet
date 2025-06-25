package com.example.intranet;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;

/**
 * Spring Boot 메인 애플리케이션 클래스
 * 
 * @author Sample Intranet Team
 * @version 1.0.0
 */
@SpringBootApplication
public class SampleIntranetApplication {

    public static void main(String[] args) {
        SpringApplication.run(SampleIntranetApplication.class, args);
        System.out.println("🚀 Sample Intranet Backend (Java) 시작 완료!");
        System.out.println("📋 API 문서: http://localhost:9090/swagger-ui.html");
        System.out.println("🏥 헬스체크: http://localhost:9090/health");
    }
} 