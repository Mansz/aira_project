import { useState, useEffect } from 'react';
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
  IconButton,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Stack,
  Chip,
  CircularProgress,
  Alert,
} from '@mui/material';
import {
  Visibility,
  Check,
  Close,
} from '@mui/icons-material';
import { api } from '@/lib/api';
import { Payment } from '@/types/payment';

interface PaymentDialogProps {
  open: boolean;
  payment: Payment | null;
  onClose: () => void;
  onVerify: () => void;
  onReject: () => void;
  loading?: boolean;
}

const PaymentDialog = ({
  open,
  payment,
  onClose,
  onVerify,
  onReject,
  loading,
}: PaymentDialogProps) => {
  if (!payment) return null;

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        Detail Pembayaran Order #{payment.order_id}
      </DialogTitle>
      <DialogContent>
        <Stack spacing={3}>
          <Box>
            <Typography variant="subtitle2" color="text.secondary">
              Status
            </Typography>
            <Box sx={{ mt: 1 }}>
              {payment.status === 'verified' && (
                <Chip
                  label={`Terverifikasi oleh ${payment.verifiedBy?.name}`}
                  color="success"
                  size="small"
                />
              )}
              {payment.status === 'rejected' && (
                <Chip
                  label="Ditolak"
                  color="error"
                  size="small"
                />
              )}
              {payment.status === 'pending' && (
                <Chip
                  label="Menunggu Verifikasi"
                  color="warning"
                  size="small"
                />
              )}
            </Box>
          </Box>

          <Box>
            <Typography variant="subtitle2" color="text.secondary">
              Metode Pembayaran
            </Typography>
            <Typography>{payment.payment_method}</Typography>
          </Box>

          <Box>
            <Typography variant="subtitle2" color="text.secondary">
              Jumlah
            </Typography>
            <Typography>
              Rp {payment.amount.toLocaleString('id-ID')}
            </Typography>
          </Box>

          {payment.bank_name && (
            <Box>
              <Typography variant="subtitle2" color="text.secondary">
                Detail Bank
              </Typography>
              <Typography>
                {payment.bank_name}<br />
                {payment.bank_account_number}<br />
                {payment.bank_account_holder}
              </Typography>
            </Box>
          )}

          {payment.card_type && (
            <Box>
              <Typography variant="subtitle2" color="text.secondary">
                Detail Kartu
              </Typography>
              <Typography>
                {payment.card_type}<br />
                **** **** **** {payment.card_last4}
              </Typography>
            </Box>
          )}
        </Stack>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Tutup</Button>
        {payment.status === 'pending' && (
          <>
            <Button
              variant="outlined"
              color="error"
              startIcon={<Close />}
              onClick={onReject}
              disabled={loading}
            >
              Tolak
            </Button>
            <Button
              variant="contained"
              startIcon={<Check />}
              onClick={onVerify}
              disabled={loading}
            >
              {loading ? <CircularProgress size={24} /> : 'Verifikasi'}
            </Button>
          </>
        )}
      </DialogActions>
    </Dialog>
  );
};

export const PaymentsPage = () => {
  const [payments, setPayments] = useState<Payment[]>([]);
  const [selectedPayment, setSelectedPayment] = useState<Payment | null>(null);
  const [loading, setLoading] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchPayments = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await api.getPayments();
      if (response.data) {
        setPayments(response.data);
      }
    } catch (err) {
      console.error(err);
      setError('Failed to load payments');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPayments();
  }, []);

  const handleVerify = async () => {
    if (!selectedPayment) return;
    try {
      setActionLoading(true);
      await api.verifyPayment(selectedPayment.id);
      await fetchPayments();
      setSelectedPayment(null);
    } catch (err) {
      console.error(err);
      setError('Failed to verify payment');
    } finally {
      setActionLoading(false);
    }
  };

  const handleReject = async () => {
    if (!selectedPayment) return;
    try {
      setActionLoading(true);
      await api.rejectPayment(selectedPayment.id);
      await fetchPayments();
      setSelectedPayment(null);
    } catch (err) {
      console.error(err);
      setError('Failed to reject payment');
    } finally {
      setActionLoading(false);
    }
  };

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        Pembayaran
      </Typography>

      {error && (
        <Alert severity="error" sx={{ mb: 2 }}>
          {error}
        </Alert>
      )}

      <Paper sx={{ p: 3 }}>
        <TableContainer>
          {loading ? (
            <Box sx={{ p: 3, textAlign: 'center' }}>
              <CircularProgress />
            </Box>
          ) : (
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>ID Order</TableCell>
                  <TableCell>Metode</TableCell>
                  <TableCell>Jumlah</TableCell>
                  <TableCell>Status</TableCell>
                  <TableCell>Diverifikasi Oleh</TableCell>
                  <TableCell>Tanggal</TableCell>
                  <TableCell align="right">Aksi</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {payments.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={7} align="center">
                      Tidak ada pembayaran
                    </TableCell>
                  </TableRow>
                ) : (
                  payments.map((payment) => (
                    <TableRow key={payment.id}>
                      <TableCell>#{payment.order_id}</TableCell>
                      <TableCell>{payment.payment_method}</TableCell>
                      <TableCell>
                        Rp {payment.amount.toLocaleString('id-ID')}
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={payment.status === 'verified' ? 'Terverifikasi' : payment.status === 'rejected' ? 'Ditolak' : 'Menunggu'}
                          color={payment.status === 'verified' ? 'success' : payment.status === 'rejected' ? 'error' : 'warning'}
                          size="small"
                        />
                      </TableCell>
                      <TableCell>
                        {payment.verifiedBy?.name || '-'}
                      </TableCell>
                      <TableCell>
                        {new Date(payment.created_at).toLocaleDateString('id-ID')}
                      </TableCell>
                      <TableCell align="right">
                        <IconButton
                          size="small"
                          onClick={() => setSelectedPayment(payment)}
                        >
                          <Visibility />
                        </IconButton>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          )}
        </TableContainer>
      </Paper>

      <PaymentDialog
        open={Boolean(selectedPayment)}
        payment={selectedPayment}
        onClose={() => setSelectedPayment(null)}
        onVerify={handleVerify}
        onReject={handleReject}
        loading={actionLoading}
      />
    </Box>
  );
};

export default PaymentsPage;
