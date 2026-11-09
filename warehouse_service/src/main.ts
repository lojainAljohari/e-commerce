import { NestFactory } from '@nestjs/core';
import { MicroserviceOptions, Transport } from '@nestjs/microservices';
import { join } from 'path';
import { AppModule } from './app.module.js';

async function bootstrap() {
  const app = await NestFactory.createMicroservice<MicroserviceOptions>(AppModule, {
    transport: Transport.GRPC,
    options: {
      package: 'warehouse',
      protoPath: join(process.cwd(), 'proto','warehouse.proto'),
      url: '0.0.0.0:50051',
    },
  });
  await app.listen();
}
bootstrap();
