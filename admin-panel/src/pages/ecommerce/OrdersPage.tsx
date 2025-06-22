import { useState, useEffect } from 'react';
import {
  Box,
  Paper,
  Typography,
  Grid,
  Card,
  CardContent,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Chip,
  IconButton,
  TextField,
  InputAdornment,
  Stack,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Stepper,
  Step,
  StepLabel,
  Menu,
  MenuItem,
  CircularProgress,
  Alert,
} from '@mui/material';
import {
  Search,
  Visibility,
  ShoppingCart,
  LocalShipping,
  AttachMoney,
  Timeline,
  FilterList,
} from '@mui/icons-material';
import { api } from '../../lib/api';
import { Order, OrderStats } from '../../types/order';

// Order status steps
const orderSteps = ['Pending', 'Processing', 'Shipped', 'Delivered'];

interface OrderDetailDialogProps {
  open: boolean;
  order?: Order;
  onClose: () => void;
}

const OrderDetailDialog = ({ open, order, onClose }: OrderDetailDialogProps) => {
  if (!order) return null;

  const getStepIndex = (status: string) => {
    const statusMap: { [key: string]: number } = {
      'Menunggu Pembayaran': 0,
      'Menunggu Konfirmasi': 0,
      'Diproses': 1,
      'Dikirim': 2,
      'Selesai': 3,
      'Dibatalkan': -1
    };
    return statusMap[status] ?? 0;
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        Detail Pesanan {order.id}
      </DialogTitle>
      <DialogContent>
        <Grid container spacing={3}>
          <Grid item xs={12}>
            <Stepper activeStep={getStepIndex(order.status)} sx={{ mb: 4 }}>
              {orderSteps.map((label) => (
                <Step key={label}>
                  <StepLabel>{label}</StepLabel>
                </Step>
              ))}
            </Stepper>
          </Grid>

          <Grid item xs={12} md={6}>
            <Card variant="outlined">
              <CardContent>
                <Typography variant="subtitle2" color="text.secondary">
                  Informasi Pelanggan
                </Typography>
                <Box sx={{ mt: 1 }}>
                  <Typography variant="body1">{order.user?.name}</Typography>
                  <Typography variant="body2">{order.user?.email}</Typography>
                  <Typography variant="body2">{order.user?.phone}</Typography>
                </Box>
              </CardContent>
            </Card>
          </Grid>

          <Grid item xs={12} md={6}>
            <Card variant="outlined">
              <CardContent>
                <Typography variant="subtitle2" color="text.secondary">
                  Alamat Pengiriman
                </Typography>
                <Typography variant="body1" sx={{ mt: 1 }}>
                  {order.shipping_address}
                </Typography>
              </CardContent>
            </Card>
          </Grid>

          <Grid item xs={12}>
            <TableContainer>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell>Produk</TableCell>
                    <TableCell align="right">Harga</TableCell>
                    <TableCell align="right">Jumlah</TableCell>
                    <TableCell align="right">Subtotal</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {order.items?.map((item, index) => (
                    <TableRow key={index}>
                      <TableCell>{item.product?.name}</TableCell>
                      <TableCell align="right">
                        Rp {item.price.toLocaleString()}
                      </TableCell>
                      <TableCell align="right">{item.quantity}</TableCell>
                      <TableCell align="right">
                        Rp {(item.price * item.quantity).toLocaleString()}
                      </TableCell>
                    </TableRow>
                  ))}
                  <TableRow>
                    <TableCell colSpan={3}>
                      <Typography variant="subtitle2">Total</Typography>
                    </TableCell>
                    <TableCell align="right">
                      <Typography variant="subtitle2">
                        Rp {order.total_amount.toLocaleString()}
                      </Typography>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </TableContainer>
          </Grid>
        </Grid>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Tutup</Button>
        <Button
          variant="contained"
          sx={{
            backgroundColor: 'black',
            '&:hover': {
              backgroundColor: '#333',
            },
          }}
        >
          Update Status
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export const OrdersPage = () => {
  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [filterAnchorEl, setFilterAnchorEl] = useState<null | HTMLElement>(null);
  const [selectedStatus, setSelectedStatus] = useState('all');
  const [orders, setOrders] = useState<Order[]>([]);
  const [orderStats, setOrderStats] = useState<OrderStats>({
    total_orders: 0,
    pending_orders: 0,
    processing_orders: 0,
    shipped_orders: 0,
    completed_orders: 0,
    cancelled_orders: 0,
    total_revenue: 0,
    today_orders: 0,
    this_month_orders: 0,
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Fetch orders and stats
  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const [ordersResponse, statsResponse] = await Promise.all([
          api.getOrders({ search: searchQuery, status: selectedStatus !== 'all' ? selectedStatus : undefined }),
          api.getOrderStats()
        ]);
        
        setOrders(ordersResponse.data);
        setOrderStats(statsResponse.data);
        setError(null);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to fetch orders');
        console.error('Error fetching orders:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [searchQuery, selectedStatus]);

  // Stats from API
  const stats = {
    totalOrders: orderStats.total_orders,
    totalRevenue: orderStats.total_revenue,
    pendingOrders: orderStats.pending_orders,
    shippedOrders: orderStats.shipped_orders,
  };

  const handleFilterClick = (event: React.MouseEvent<HTMLElement>) => {
    setFilterAnchorEl(event.currentTarget);
  };

  const handleFilterClose = () => {
    setFilterAnchorEl(null);
  };

  const handleStatusFilter = (status: string) => {
    setSelectedStatus(status);
    handleFilterClose();
  };

  const getStatusColor = (status: string): "success" | "warning" | "error" | "info" => {
    switch (status.toLowerCase()) {
      case 'selesai':
      case 'delivered':
        return 'success';
      case 'dikirim':
      case 'shipped':
        return 'info';
      case 'diproses':
      case 'processing':
        return 'warning';
      case 'menunggu pembayaran':
      case 'menunggu konfirmasi':
      case 'pending':
        return 'error';
      default:
        return 'warning';
    }
  };

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        Pesanan
      </Typography>

      <Grid container spacing={3} sx={{ mb: 4 }}>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <ShoppingCart sx={{ fontSize: 40, color: 'primary.main' }} />
                <Typography variant="h4">{stats.totalOrders}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Total Pesanan
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <AttachMoney sx={{ fontSize: 40, color: 'success.main' }} />
                <Typography variant="h4">
                  Rp {stats.totalRevenue.toLocaleString()}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  Total Pendapatan
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <Timeline sx={{ fontSize: 40, color: 'warning.main' }} />
                <Typography variant="h4">{stats.pendingOrders}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Pesanan Pending
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Stack spacing={1}>
                <LocalShipping sx={{ fontSize: 40, color: 'info.main' }} />
                <Typography variant="h4">{stats.shippedOrders}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Sedang Dikirim
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      <Paper sx={{ p: 3 }}>
        <Box sx={{ mb: 3, display: 'flex', gap: 2 }}>
          <TextField
            fullWidth
            placeholder="Cari pesanan..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Search />
                </InputAdornment>
              ),
            }}
          />
          <Button
            startIcon={<FilterList />}
            onClick={handleFilterClick}
            sx={{ color: 'black' }}
          >
            Filter
          </Button>
        </Box>

        {error && (
          <Alert severity="error" sx={{ mb: 3 }}>
            {error}
          </Alert>
        )}

        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', p: 3 }}>
            <CircularProgress />
          </Box>
        ) : (
          <TableContainer>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>ID Pesanan</TableCell>
                  <TableCell>Pelanggan</TableCell>
                  <TableCell>Total</TableCell>
                  <TableCell>Status</TableCell>
                  <TableCell>Pembayaran</TableCell>
                  <TableCell>Pengiriman</TableCell>
                  <TableCell>Tanggal</TableCell>
                  <TableCell align="right">Aksi</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {orders.map((order) => (
                  <TableRow key={order.id}>
                    <TableCell>{order.id}</TableCell>
                    <TableCell>
                      <Stack>
                        <Typography variant="body2">{order.user?.name}</Typography>
                        <Typography variant="caption" color="text.secondary">
                          {order.user?.email}
                        </Typography>
                      </Stack>
                    </TableCell>
                    <TableCell>Rp {order.total_amount.toLocaleString()}</TableCell>
                    <TableCell>
                      <Chip
                        size="small"
                        label={order.status}
                        color={getStatusColor(order.status.toLowerCase())}
                      />
                    </TableCell>
                    <TableCell>
                      <Chip
                        size="small"
                        label={order.payment_proof ? 'Paid' : 'Unpaid'}
                        color={order.payment_proof ? 'success' : 'error'}
                      />
                    </TableCell>
                    <TableCell>
                      <Chip
                        size="small"
                        label={order.shipping_status || 'Pending'}
                        color={getStatusColor(order.shipping_status?.toLowerCase() || 'pending')}
                      />
                    </TableCell>
                    <TableCell>
                      {new Date(order.created_at).toLocaleDateString('id-ID')}
                    </TableCell>
                    <TableCell align="right">
                      <IconButton
                        size="small"
                        onClick={() => setSelectedOrder(order)}
                        sx={{ color: 'black' }}
                      >
                        <Visibility />
                      </IconButton>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>
        )}
      </Paper>

      <Menu
        anchorEl={filterAnchorEl}
        open={Boolean(filterAnchorEl)}
        onClose={handleFilterClose}
      >
        <MenuItem onClick={() => handleStatusFilter('all')}>
          Semua Status
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Menunggu Pembayaran')}>
          Menunggu Pembayaran
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Menunggu Konfirmasi')}>
          Menunggu Konfirmasi
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Diproses')}>
          Diproses
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Dikirim')}>
          Dikirim
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Selesai')}>
          Selesai
        </MenuItem>
        <MenuItem onClick={() => handleStatusFilter('Dibatalkan')}>
          Dibatalkan
        </MenuItem>
      </Menu>

      <OrderDetailDialog
        open={Boolean(selectedOrder)}
        order={selectedOrder || undefined}
        onClose={() => setSelectedOrder(null)}
      />
    </Box>
  );
};
