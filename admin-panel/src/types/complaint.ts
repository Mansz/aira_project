import { Admin } from './admin';
import { Order } from './order';

export interface OrderComplaint {
    id: number;
    order_id: number;
    description: string;
    photo_path: string | null;
    photo_url: string | null;
    status: 'Pending' | 'Processing' | 'Resolved' | 'Rejected';
    status_label: string;
    status_color: string;
    admin_notes: string | null;
    resolved_at: string | null;
    resolved_by: number | null;
    created_at: string;
    updated_at: string;
    order?: Order;
    resolvedBy?: Admin;
}

export interface OrderComplaintStats {
    total: number;
    pending: number;
    processing: number;
    resolved: number;
    rejected: number;
}
