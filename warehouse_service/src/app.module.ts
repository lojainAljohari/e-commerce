import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { Reservation, Stock } from './stock.entity.js';
import { WarehouseController } from './warehouse.controller.js';
import { WarehouseService } from './warehouse.service.js';

@Module({
  imports: [
    TypeOrmModule.forRoot({
      type: 'mysql',
      host: process.env.DB_HOST ?? 'localhost',
      port: Number(process.env.DB_PORT ?? 3306),
      username: process.env.DB_USER ?? 'warehouse',
      password: process.env.DB_PASSWORD ?? 'warehouse',
      database: process.env.DB_NAME ?? 'warehouse',
      entities: [Stock, Reservation],
      synchronize: true,
    }),
    TypeOrmModule.forFeature([Stock, Reservation]),
  ],
  controllers: [WarehouseController],
  providers: [WarehouseService],
})
export class AppModule {}
