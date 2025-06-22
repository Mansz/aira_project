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
  TextField,
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
import { PaymentProof } from '@/types/paymentProof';

interface PaymentProofDialogProps {
  open: boolean;
  paymentProof: PaymentProof | null;
  onClose: () => void;
  onVerify: () => void;
  onReject: (notes: string) => void;
  loading?: boolean;
}

const PaymentProofDialog = ({
  open,
  paymentProof,
  onClose,
  onVerify,
  onReject,
  loading,
}: PaymentProofDialogProps) => {
  const [rejectionNotes, setRejectionNotes] = useState('');
  const [showRejectForm, setShowRejectForm] = useState(false);

  if (!paymentProof) return null;

  const handleReject = () => {
    onReject(rejectionNotes);
    setRejectionNotes('');
    setShowRejectForm(false);
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        Bukti Pembayaran Order #{paymentProof.order_id}
      </DialogTitle>
      <DialogContent>
        <Stack spacing={3}>
          <Box sx={{ mt: 2 }}>
            <img 
              src={`/storage/${paymentProof.file_path}`} 
              alt="Bukti Pembayaran" 
              style={{ 
                width: '100%', 
                maxHeight: '500px', 
                objectFit: 'contain' 
              }} 
            />
          </Box>

          <Box>
            <Typography variant="subtitle2" color="text.secondary">
              Status
            </Typography>
            <Box sx={{ mt: 1 }}>
              {paymentProof.status === 'verified' && (
                <Chip
                  label={`Terverifikasi oleh ${paymentProof.verifiedBy?.name}`}
                  color="success"
                  size="small"
                />
              )}
              {paymentProof.status === 'rejected' && (
                <Chip
                  label="Ditolak"
                  color="error"
                  size="small"
                />
              )}
              {paymentProof.status === 'pending' && (
                <Chip
                  label="Menunggu Verifikasi"
                  color="warning"
                  size="small"
                />
              )}
            </Box>
          </Box>

          {paymentProof.status === 'rejected' && paymentProof.notes && (
            <Box>
              <Typography variant="subtitle2" color="text.secondary">
                Alasan Penolakan
              </Typography>
              <Typography>{paymentProof.notes}</Typography>
            </Box>
          )}

          {showRejectForm && (
            <TextField
              fullWidth
              label="Alasan Penolakan"
              multiline
              rows={3}
              value={rejectionNotes}
              onChange={(e) => setRejectionNotes(e.target.value)}
            />
          )}
        </Stack>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Tutup</Button>
        {paymentProof.status === 'pending' && (
          <>
            {!showRejectForm ? (
              <>
                <Button
                  variant="outlined"
                  color="error"
                  startIcon={<Close />}
                  onClick={() => setShowRejectForm(true)}
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
            ) : (
              <>
                <Button
                  onClick={() => setShowRejectForm(false)}
                >
                  Batal
                </Button>
                <Button
                  variant="contained"
                  color="error"
                  onClick={handleReject}
                  disabled={!rejectionNotes || loading}
                >
                  {loading ? <CircularProgress size={24} /> : 'Kirim Penolakan'}
                </Button>
              </>
            )}
          </>
        )}
      </DialogActions>
    </Dialog>
  );
};

export const PaymentProofsPage = () => {
  const [paymentProofs, setPaymentProofs] = useState<PaymentProof[]>([]);
  const [selectedProof, setSelectedProof] = useState<PaymentProof | null>(null);
  const [loading, setLoading] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchPaymentProofs = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await api.getPaymentProofs();
      setPaymentProofs(response.data);
    } catch (err) {
      console.error(err);
      setError('Failed to load payment proofs');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPaymentProofs();
  }, []);

  const handleVerify = async () => {
    if (!selectedProof) return;
    try {
      setActionLoading(true);
      await api.verifyPaymentProof(selectedProof.id);
      await fetchPaymentProofs();
      setSelectedProof(null);
    } catch (err) {
      console.error(err);
      setError('Failed to verify payment proof');
    } finally {
      setActionLoading(false);
    }
  };

  const handleReject = async (notes: string) => {
    if (!selectedProof) return;
    try {
      setActionLoading(true);
      await api.rejectPaymentProof(selectedProof.id, notes);
      await fetchPaymentProofs();
      setSelectedProof(null);
    } catch (err) {
      console.error(err);
      setError('Failed to reject payment proof');
    } finally {
      setActionLoading(false);
    }
  };

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        Bukti Pembayaran
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
                  <TableCell>Tanggal Upload</TableCell>
                  <TableCell>Status</TableCell>
                  <TableCell>Diverifikasi/Ditolak Oleh</TableCell>
                  <TableCell>Catatan</TableCell>
                  <TableCell align="right">Aksi</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {paymentProofs.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={6} align="center">
                      Tidak ada bukti pembayaran
                    </TableCell>
                  </TableRow>
                ) : (
                  paymentProofs.map((proof) => (
                    <TableRow key={proof.id}>
                      <TableCell>#{proof.order_id}</TableCell>
                      <TableCell>
                        {new Date(proof.created_at).toLocaleDateString('id-ID')}
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={proof.status === 'verified' ? 'Terverifikasi' : proof.status === 'rejected' ? 'Ditolak' : 'Menunggu'}
                          color={proof.status === 'verified' ? 'success' : proof.status === 'rejected' ? 'error' : 'warning'}
                          size="small"
                        />
                      </TableCell>
                      <TableCell>
                        {proof.verifiedBy?.name || '-'}
                      </TableCell>
                      <TableCell>
                        {proof.notes || '-'}
                      </TableCell>
                      <TableCell align="right">
                        <IconButton
                          size="small"
                          onClick={() => setSelectedProof(proof)}
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

      <PaymentProofDialog
        open={Boolean(selectedProof)}
        paymentProof={selectedProof}
        onClose={() => setSelectedProof(null)}
        onVerify={handleVerify}
        onReject={handleReject}
        loading={actionLoading}
      />
    </Box>
  );
};

export default PaymentProofsPage;
