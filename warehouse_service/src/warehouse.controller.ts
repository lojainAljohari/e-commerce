import { Controller } from '@nestjs/common';
import { GrpcMethod } from '@nestjs/microservices';
import { WarehouseService } from './warehouse.service.js';

@Controller()
export class WarehouseController {
  constructor(private readonly svc: WarehouseService) {}

  @GrpcMethod('Warehouse', 'GetQuantity')
  async getQuantity(d: { productId: number }) {
    return { productId: d.productId, available: await this.svc.getQuantity(d.productId) };
  }

  @GrpcMethod('Warehouse', 'Reserve')
  reserve(d: { reservationId: string; items: { productId: number; quantity: number }[] }) {
    return this.svc.reserve(d.reservationId, d.items ?? []);
  }

  @GrpcMethod('Warehouse', 'Release')
  async release(d: { reservationId: string }) {
    return { success: await this.svc.release(d.reservationId) };
  }
}
