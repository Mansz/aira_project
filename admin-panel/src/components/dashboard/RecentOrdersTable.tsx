import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Typography,
  Chip,
  IconButton,
  Skeleton,
} from '@mui/material';
import { Visibility } from '@mui/icons-material';

// Definisi types langsung di dalam file untuk mengatasi error import
interface RecentOrder {
  id: number;
  customer_name: string;
  total_amount: number;
  status: string;
  created_at: string;
}

interface DashboardStats {
  recentOrders: RecentOrder[];
}

interface RecentOrdersTableProps {
  loading: boolean;
  orders: DashboardStats['recentOrders'];
  onViewOrder?: (orderId: number) => void;
}

const getStatusColor = (status: string): "success" | "warning" | "error" | "info" => {
  switch (status.toLowerCase()) {
    case 'completed':
      return 'success';
    case 'pending':
      return 'warning';
    case 'active':
      return 'info';
    default:
      return 'error';
  }
};

export const RecentOrdersTable = ({ loading, orders, onViewOrder }: RecentOrdersTableProps) => (
  <Paper sx={{ p: 3 }}>
    <Typography variant="h6" sx={{ mb: 3 }}>
      Recent Orders
    </Typography>
    <TableContainer>
      <Table>
        <TableHead>
          <TableRow>
            <TableCell>Order ID</TableCell>
            <TableCell>Customer</TableCell>
            <TableCell>Total</TableCell>
            <TableCell>Status</TableCell>
            <TableCell>Date</TableCell>
            <TableCell align="right">Action</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {loading ? (
            [...Array(3)].map((_, index) => (
              <TableRow key={index}>
                <TableCell><Skeleton /></TableCell>
                <TableCell><Skeleton /></TableCell>
                <TableCell><Skeleton /></TableCell>
                <TableCell><Skeleton /></TableCell>
                <TableCell><Skeleton /></TableCell>
                <TableCell align="right"><Skeleton /></TableCell>
              </TableRow>
            ))
          ) : (
            orders?.map((order) => (
              <TableRow key={order.id}>
                <TableCell>#{order.id}</TableCell>
                <TableCell>{order.customer_name}</TableCell>
                <TableCell>
                  Rp {order.total_amount.toLocaleString()}
                </TableCell>
                <TableCell>
                  <Chip
                    size="small"
                    label={order.status}
                    color={getStatusColor(order.status)}
                  />
                </TableCell>
                <TableCell>
                  {new Date(order.created_at).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                  })}
                </TableCell>
                <TableCell align="right">
                  <IconButton 
                    size="small" 
                    sx={{ color: 'black' }}
                    onClick={() => onViewOrder?.(order.id)}
                  >
                    <Visibility />
                  </IconButton>
                </TableCell>
              </TableRow>
            ))
          )}
        </TableBody>
      </Table>
    </TableContainer>
  </Paper>
);

// Export types agar bisa digunakan di file lain
export type { RecentOrder, DashboardStats };