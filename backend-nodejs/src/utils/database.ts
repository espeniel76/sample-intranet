import { PrismaClient } from '@prisma/client';

/**
 * Prisma 클라이언트 싱글톤 인스턴스
 * 데이터베이스 연결 관리
 */
class DatabaseClient {
  private static instance: PrismaClient;
  
  private constructor() {}
  
  public static getInstance(): PrismaClient {
    if (!DatabaseClient.instance) {
      DatabaseClient.instance = new PrismaClient({
        log: ['query', 'info', 'warn', 'error'],
      });
      console.log('🗄️  데이터베이스 연결 초기화 완료');
    }
    return DatabaseClient.instance;
  }
  
  /**
   * 데이터베이스 연결 닫기
   */
  public static async disconnect(): Promise<void> {
    if (DatabaseClient.instance) {
      await DatabaseClient.instance.$disconnect();
      console.log('🗄️  데이터베이스 연결 종료');
    }
  }
  
  /**
   * 데이터베이스 연결 상태 확인
   */
  public static async checkConnection(): Promise<boolean> {
    try {
      const client = DatabaseClient.getInstance();
      await client.$queryRaw`SELECT 1`;
      return true;
    } catch (error) {
      console.error('데이터베이스 연결 실패:', error);
      return false;
    }
  }
}

// Prisma 클라이언트 인스턴스 내보내기
export const prisma = DatabaseClient.getInstance();

// 데이터베이스 유틸리티 함수들 내보내기
export const disconnectDatabase = DatabaseClient.disconnect;
export const checkDatabaseConnection = DatabaseClient.checkConnection; 