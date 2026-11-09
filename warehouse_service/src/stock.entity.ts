import { Column, Entity, PrimaryColumn } from 'typeorm';

@Entity('stock')
export class Stock {
  @PrimaryColumn() productId: number;
  @Column({ default: 0 }) quantity: number;
}

@Entity('reservations')
export class Reservation {
  @PrimaryColumn() reservationId: string;
  @Column('json') items: { productId: number; quantity: number }[];
  @Column({ default: 'RESERVED' }) status: string;
}
