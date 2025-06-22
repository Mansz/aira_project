import { useState, useEffect, useCallback } from 'react';
import Echo from '@/lib/echo';
import { LiveOrder, LiveOrderProduct, NewOrderEvent, OrderUpdatedEvent, OrderConfirmedEvent } from '@/types/stream';
import {
  Box,
  Paper,
  Typography,
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
  Grid,
  Card,
  CardContent,
  CircularProgress,
} from '@mui/material';
import {
  Search,
  Visibility,
  ShoppingCart,
  AttachMoney,
  Timeline,
  Receipt,
} from '@mui/icons-material';
import { api } from '@/lib/api';

interface OrderDetailDialogProps {
  open: boolean;
  order?: LiveOrder;
  onClose: () => void;
}

const OrderDetailDialog = ({ open, order, onClose }: OrderDetailDialogProps) => {
  if (!order) return null;

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        Detail Pesanan {order.id}
      </DialogTitle>
      <DialogContent>
        <Grid container spacing={3}>
          <Grid item xs={12} md={6}>
            <Card variant="outlined">
              <CardContent>
                <Typography variant="subtitle2" color="text.secondary">
                  Informasi Pelanggan
                </Typography>
                <Typography variant="body1" sx={{ mt: 1 }}>
                  {order.customer.name}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  {order.customer.phone}
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={12} md={6}>
            <Card variant="outlined">
              <CardContent>
                <Typography variant="subtitle2" color="text.secondary">
                  Status Pesanan
                </Typography>
                <Stack direction="row" spacing={1} sx={{ mt: 1 }}>
                  <Chip
                    size="small"
                    label={order.status}
                    color={order.status === 'confirmed' ? 'success' : 'warning'}
                  />
                  <Chip
                    size="small"
                    label={order.paymentStatus}
                    color={order.paymentStatus === 'paid' ? 'success' : 'error'}
                  />
                </Stack>
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
                  {order.products.map((product: LiveOrderProduct, index: number) => (
                    <TableRow key={index}>
                      <TableCell>{product.name}</TableCell>
                      <TableCell align="right">
                        Rp {product.price.toLocaleString()}
                      </TableCell>
                      <TableCell align="right">{product.quantity}</TableCell>
                      <TableCell align="right">
                        Rp {(product.price * product.quantity).toLocaleString()}
                      </TableCell>
                    </TableRow>
                  ))}
                  <TableRow>
                    <TableCell colSpan={3}>
                      <Typography variant="subtitle2">Total</Typography>
                    </TableCell>
                    <TableCell align="right">
                      <Typography variant="subtitle2">
                        Rp {order.total.toLocaleString()}
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
        {order.status === 'pending' && (
          <Button
            variant="contained"
            onClick={async () => {
              try {
                await api.confirmLiveOrder(order.id);
                // Update akan diterima melalui WebSocket
              } catch (err: any) {
                console.error('Failed to confirm order:', err);
              }
            }}
            sx={{
              backgroundColor: 'black',
              '&:hover': {
                backgroundColor: '#333',
              },
            }}
          >
            Konfirmasi Pesanan
          </Button>
        )}
      </DialogActions>
    </Dialog>
  );
};

export const LiveOrdersPage = () => {
  const [selectedOrder, setSelectedOrder] = useState<LiveOrder | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [orders, setOrders] = useState<LiveOrder[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string>('');

  const handleOpenDetail = (order: LiveOrder) => {
    setSelectedOrder(order);
  };

  const handleCloseDetail = () => {
    setSelectedOrder(null);
  };

  const fetchOrders = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const response = await api.getLiveOrders();
      setOrders(response.data);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch live orders');
    } finally {
      setLoading(false);
    }
  }, []);

  // Initial fetch
  useEffect(() => {
    fetchOrders();
  }, [fetchOrders]);

  // Real-time updates
  useEffect(() => {
    // Subscribe to new orders
    const channel = Echo.channel('live-orders');
    
    channel.listen('NewOrder', (e: NewOrderEvent) => {
      setOrders(prev => [e.order, ...prev]);
    });

    channel.listen('OrderUpdated', (e: OrderUpdatedEvent) => {
      setOrders(prev => 
        prev.map(order => 
          order.id === e.order.id ? e.order : order
        )
      );
    });

    channel.listen('OrderConfirmed', (e: OrderConfirmedEvent) => {
      setOrders(prev => 
        prev.map(order => 
          order.id === e.order.id 
            ? { ...order, status: 'confirmed' }
            : order
        )
      );
    });

    return () => {
      channel.stopListening('NewOrder');
      channel.stopListening('OrderUpdated');
      channel.stopListening('OrderConfirmed');
    };
  }, []);

  // Stats calculation
  const stats = {
    totalOrders: orders.length,
    totalRevenue: orders.reduce((sum, order) => sum + order.total, 0),
    pendingOrders: orders.filter(order => order.status === 'pending').length,
    confirmedOrders: orders.filter(order => order.status === 'confirmed').length,
  };

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        Pesanan Live
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
                <Receipt sx={{ fontSize: 40, color: 'info.main' }} />
                <Typography variant="h4">{stats.confirmedOrders}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Pesanan Dikonfirmasi
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      <Paper sx={{ p: 3 }}>
        <Box sx={{ mb: 3 }}>
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
        </Box>

        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', p: 3 }}>
            <CircularProgress />
          </Box>
        ) : error ? (
          <Typography color="error" align="center" sx={{ p: 3 }}>
            {error}
          </Typography>
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
                  <TableCell>Waktu Pesan</TableCell>
                  <TableCell align="right">Aksi</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {orders
                  .filter(order =>
                    order.id.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    order.customer.name.toLowerCase().includes(searchQuery.toLowerCase())
                  )
                  .map((order) => (
                    <TableRow key={order.id}>
                      <TableCell>{order.id}</TableCell>
                      <TableCell>
                        <Stack>
                          <Typography variant="body2">{order.customer.name}</Typography>
                          <Typography variant="caption" color="text.secondary">
                            {order.customer.phone}
                          </Typography>
                        </Stack>
                      </TableCell>
                      <TableCell>Rp {order.total.toLocaleString()}</TableCell>
                      <TableCell>
                        <Chip
                          size="small"
                          label={order.status}
                          color={order.status === 'confirmed' ? 'success' : 'warning'}
                        />
                      </TableCell>
                      <TableCell>
                        <Chip
                          size="small"
                          label={order.paymentStatus}
                          color={order.paymentStatus === 'paid' ? 'success' : 'error'}
                        />
                      </TableCell>
                      <TableCell>
                        {new Date(order.orderTime).toLocaleTimeString('id-ID')}
                      </TableCell>
                      <TableCell align="right">
                        <IconButton
                          size="small"
                          onClick={() => handleOpenDetail(order)}
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

      <OrderDetailDialog
        open={Boolean(selectedOrder)}
        order={selectedOrder}
        onClose={handleCloseDetail}
      />
    </Box>
  );
};
