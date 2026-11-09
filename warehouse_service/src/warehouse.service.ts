import { Injectable } from '@nestjs/common';
import { InjectDataSource } from '@nestjs/typeorm';
import { DataSource } from 'typeorm';
import { Reservation, Stock } from './stock.entity.js';

class OutOfStock extends Error {
    constructor(public productId: number, public requested: number) { super('out of stock'); }
}

@Injectable()
export class WarehouseService {
    constructor(@InjectDataSource() private ds: DataSource) { }

    async getQuantity(productId: number): Promise<number> {
        const s = await this.ds.getRepository(Stock).findOneBy({ productId });
        return s?.quantity ?? 0;
    }

    async reserve(reservationId: string, items: { productId: number; quantity: number }[]) {
        try {
            await this.ds.transaction(async (m) => {
                if (await m.existsBy(Reservation, { reservationId })) return;
                const sorted = [...items].sort((a, b) => a.productId - b.productId);
                for (const it of sorted) {

                    const res: any = await m.query(
                        'UPDATE stock SET quantity = quantity - ? WHERE productId = ? AND quantity >= ?',
                        [it.quantity, it.productId, it.quantity],
                    );
                    if (res.affectedRows !== 1) throw new OutOfStock(it.productId, it.quantity);
                }
                await m.insert(Reservation, { reservationId, items: sorted, status: 'RESERVED' });
            });
            return { success: true, message: 'done reserve' };
        } catch (e) {
            if (e instanceof OutOfStock) {
                const available = await this.getQuantity(e.productId);
                return {
                    success: false,
                    message: 'quantity is not enough',
                };
            }
            throw e;
        }
    }

    async release(reservationId: string): Promise<boolean> {
        return this.ds.transaction(async (m) => {
            const r = await m.findOne(Reservation, { where: { reservationId }, lock: { mode: 'pessimistic_write' } });
            if (!r || r.status !== 'RESERVED') return false;
            for (const it of r.items) {
                await m.query('UPDATE stock SET quantity = quantity + ? WHERE productId = ?', [it.quantity, it.productId]);
            }
            await m.update(Reservation, { reservationId }, { status: 'RELEASED' });
            return true;
        });
    }
}
