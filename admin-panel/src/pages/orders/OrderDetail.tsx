import { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
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
  Button,
  Stepper,
  Step,
  StepLabel,
  Stack,
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  TextField,
} from '@mui/material';
import {
  ArrowBack,
  Edit,
} from '@mui/icons-material';

import { api } from '@/lib/api';
import { Order, OrderItem } from '@/types/order';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { toast } from 'sonner';

// Order status steps
const orderSteps = [
  'Menunggu Pembayaran',
  'Menunggu Konfirmasi',
  'Diproses',
  'Dikirim',
  'Selesai'
];

export const OrderDetail = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [updateDialogOpen, setUpdateDialogOpen] = useState(false);
  const [newStatus, setNewStatus] = useState('');
  const [trackingNumber, setTrackingNumber] = useState('');
  const [shippingCourier, setShippingCourier] = useState('');

  const { data: order, isLoading } = useQuery({
    queryKey: ['order', id],
    queryFn: async () => {
      try {
        const response = await api.getOrder(id!);
        return response.data;
      } catch (error) {
        toast.error('Failed to fetch order');
        return null;
      }
    }
  });

  const updateOrderMutation = useMutation({
    mutationFn: async (data: { status: string; tracking_number?: string; shipping_courier?: string }) => {
      const response = await api.updateOrderStatus(id!, data.status);
      
      // If shipping info is provided, update it separately
      if (data.tracking_number || data.shipping_courier) {
        await api.updateShipmentTracking(id!, {
          tracking_number: data.tracking_number || '',
          courier_name: data.shipping_courier
        });
      }
      
      return response.data;
    },
    onSuccess: () => {
      toast.success('Order status updated successfully');
      queryClient.invalidateQueries({ queryKey: ['order', id] });
      queryClient.invalidateQueries({ queryKey: ['orders'] });
      setUpdateDialogOpen(false);
      setNewStatus('');
      setTrackingNumber('');
      setShippingCourier('');
    },
    onError: (error: any) => {
      toast.error(error.message || 'Failed to update order status');
    }
  });

  const handleUpdateStatus = () => {
    if (!newStatus) {
      toast.error('Please select a status');
      return;
    }

    updateOrderMutation.mutate({
      status: newStatus,
      tracking_number: trackingNumber,
      shipping_courier: shippingCourier
    });
  };

  const openUpdateDialog = () => {
    setNewStatus(order?.status || '');
    setTrackingNumber(order?.tracking_number || '');
    setShippingCourier(order?.shipping_courier || '');
    setUpdateDialogOpen(true);
  };

  if (!order) {
    return (
      <Box>
        <Typography variant="h4">Order not found</Typography>
        <Button onClick={() => navigate('/orders')} startIcon={<ArrowBack />}>
          Back to Orders
        </Button>
      </Box>
    );
  }

  const getStepIndex = (status: string) => {
    return orderSteps.findIndex(
      (step) => step.toLowerCase() === status.toLowerCase()
    );
  };

  const getStatusColor = (status: string): "success" | "warning" | "error" | "info" => {
    switch (status) {
      case 'Selesai':
        return 'success';
      case 'Dikirim':
        return 'info';
      case 'Diproses':
        return 'warning';
      case 'Menunggu Pembayaran':
      case 'Menunggu Konfirmasi':
        return 'error';
      case 'Dibatalkan':
        return 'error';
      default:
        return 'warning';
    }
  };

  return (
    <Box>
      <Box sx={{ mb: 3, display: 'flex', alignItems: 'center', gap: 2 }}>
        <IconButton onClick={() => navigate('/orders')}>
          <ArrowBack />
        </IconButton>
        <Typography variant="h4">
          Order Detail - {order.id}
        </Typography>
        <Button
          variant="contained"
          startIcon={<Edit />}
          onClick={openUpdateDialog}
          sx={{
            ml: 'auto',
            backgroundColor: 'black',
            '&:hover': {
              backgroundColor: '#333',
            },
          }}
        >
          Update Status
        </Button>
      </Box>

      <Grid container spacing={3}>
        <Grid item xs={12}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" sx={{ mb: 3 }}>
              Order Progress
            </Typography>
            <Stepper activeStep={getStepIndex(order.status)} sx={{ mb: 4 }}>
              {orderSteps.map((label) => (
                <Step key={label}>
                  <StepLabel>{label}</StepLabel>
                </Step>
              ))}
            </Stepper>
          </Paper>
        </Grid>

        <Grid item xs={12} md={6}>
          <Card>
            <CardContent>
              <Typography variant="h6" sx={{ mb: 2 }}>
                Customer Information
              </Typography>
              <Stack spacing={1}>
                <Typography variant="body1">
                  <strong>Name:</strong> {order.user?.name || 'Unknown'}
                </Typography>
                <Typography variant="body1">
                  <strong>Email:</strong> {order.user?.email || 'No email'}
                </Typography>
                <Typography variant="body1">
                  <strong>Phone:</strong> {order.user?.phone || 'No phone'}
                </Typography>
              </Stack>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={6}>
          <Card>
            <CardContent>
              <Typography variant="h6" sx={{ mb: 2 }}>
                Order Information
              </Typography>
              <Stack spacing={1}>
                <Typography variant="body1">
                  <strong>Order Date:</strong> {new Date(order.created_at).toLocaleDateString('id-ID')}
                </Typography>
                <Box sx={{ display: 'flex', gap: 1, alignItems: 'center' }}>
                  <strong>Status:</strong>
                  <Chip
                    size="small"
                    label={order.status}
                    color={getStatusColor(order.status)}
                  />
                </Box>
                <Box sx={{ display: 'flex', gap: 1, alignItems: 'center' }}>
                  <strong>Payment:</strong>
                  <Chip
                    size="small"
                    label={order.payment_method || 'Belum Bayar'}
                    color={order.payment_proof?.verified_at ? 'success' : 'error'}
                  />
                </Box>
                <Box sx={{ display: 'flex', gap: 1, alignItems: 'center' }}>
                  <strong>Shipping:</strong>
                  <Chip
                    size="small"
                    label={order.shipping_courier || 'Belum Dikirim'}
                    color={order.tracking_number ? 'success' : 'warning'}
                  />
                </Box>
                {order.tracking_number && (
                  <Typography variant="body1">
                    <strong>Tracking Number:</strong> {order.tracking_number}
                  </Typography>
                )}
              </Stack>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12}>
          <Card>
            <CardContent>
              <Typography variant="h6" sx={{ mb: 2 }}>
                Shipping Address
              </Typography>
              <Typography variant="body1">
                {order.shipping_address}
              </Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" sx={{ mb: 2 }}>
              Order Items
            </Typography>
            <TableContainer>
              <Table>
                <TableHead>
                  <TableRow>
                    <TableCell>Product</TableCell>
                    <TableCell align="right">Price</TableCell>
                    <TableCell align="right">Quantity</TableCell>
                    <TableCell align="right">Subtotal</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {order.items?.map((item: OrderItem) => (
                    <TableRow key={item.id}>
                      <TableCell>{item.product?.name || 'Unknown Product'}</TableCell>
                      <TableCell align="right">
                        Rp {Number(item.price).toLocaleString()}
                      </TableCell>
                      <TableCell align="right">{item.quantity}</TableCell>
                      <TableCell align="right">
                        Rp {(Number(item.price) * item.quantity).toLocaleString()}
                      </TableCell>
                    </TableRow>
                  ))}
                  <TableRow>
                    <TableCell colSpan={3}>
                      <Typography variant="subtitle1" sx={{ fontWeight: 'bold' }}>
                        Total
                      </Typography>
                    </TableCell>
                    <TableCell align="right">
                      <Typography variant="subtitle1" sx={{ fontWeight: 'bold' }}>
                        Rp {Number(order.total_amount).toLocaleString()}
                      </Typography>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </TableContainer>
          </Paper>
        </Grid>
      </Grid>

      {/* Update Status Dialog */}
      <Dialog open={updateDialogOpen} onClose={() => setUpdateDialogOpen(false)} maxWidth="sm" fullWidth>
        <DialogTitle>Update Order Status</DialogTitle>
        <DialogContent>
          <Stack spacing={3} sx={{ mt: 1 }}>
            <FormControl fullWidth>
              <InputLabel>Status</InputLabel>
              <Select
                value={newStatus}
                onChange={(e) => setNewStatus(e.target.value)}
                label="Status"
              >
                <MenuItem value="Menunggu Pembayaran">Menunggu Pembayaran</MenuItem>
                <MenuItem value="Menunggu Konfirmasi">Menunggu Konfirmasi</MenuItem>
                <MenuItem value="Diproses">Diproses</MenuItem>
                <MenuItem value="Dikirim">Dikirim</MenuItem>
                <MenuItem value="Selesai">Selesai</MenuItem>
                <MenuItem value="Dibatalkan">Dibatalkan</MenuItem>
              </Select>
            </FormControl>

            {(newStatus === 'Dikirim' || newStatus === 'Selesai') && (
              <>
                <TextField
                  fullWidth
                  label="Tracking Number"
                  value={trackingNumber}
                  onChange={(e) => setTrackingNumber(e.target.value)}
                  placeholder="Enter tracking number"
                />
                <TextField
                  fullWidth
                  label="Shipping Courier"
                  value={shippingCourier}
                  onChange={(e) => setShippingCourier(e.target.value)}
                  placeholder="e.g., JNE, TIKI, POS"
                />
              </>
            )}
          </Stack>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setUpdateDialogOpen(false)}>Cancel</Button>
          <Button 
            onClick={handleUpdateStatus} 
            variant="contained"
            disabled={updateOrderMutation.isPending}
            sx={{
              backgroundColor: 'black',
              '&:hover': {
                backgroundColor: '#333',
              },
            }}
          >
            {updateOrderMutation.isPending ? 'Updating...' : 'Update'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};
