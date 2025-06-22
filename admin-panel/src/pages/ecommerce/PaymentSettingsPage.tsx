import { useState, useEffect } from 'react';
import {
  Box,
  Paper,
  Typography,
  Grid,
  Card,
  CardContent,
  CardActions,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Switch,
  FormControlLabel,
  Stack,
  IconButton,
  CircularProgress,
  Alert,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Chip,
} from '@mui/material';
import {
  Add as AddIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  AccountBalance,
  AccountBalanceWallet,
} from '@mui/icons-material';
import { api, ApiError } from '@/lib/api';

import { PaymentSetting, PaymentSettingFormData } from '@/types/paymentSetting';

interface PaymentSettingDialogProps {
  open: boolean;
  onClose: () => void;
  onSubmit: (data: FormData) => void;
  initialData?: PaymentSetting;
  loading?: boolean;
}

const PaymentSettingDialog = ({
  open,
  onClose,
  onSubmit,
  initialData,
  loading,
}: PaymentSettingDialogProps) => {
  const [formData, setFormData] = useState<PaymentSettingFormData>({
    payment_type: 'bank_transfer',
    name: '',
    account_number: '',
    account_name: '',
    description: '',
    is_active: true,
    instructions: [''],
  });

  useEffect(() => {
    if (initialData) {
      setFormData({
        payment_type: initialData.payment_type,
        name: initialData.name,
        account_number: initialData.account_number,
        account_name: initialData.account_name,
        description: initialData.description || '',
        is_active: initialData.is_active,
        instructions: initialData.instructions,
      });
    } else {
      setFormData({
        payment_type: 'bank_transfer',
        name: '',
        account_number: '',
        account_name: '',
        description: '',
        is_active: true,
        instructions: [''],
      });
    }
  }, [initialData]);

  const handleSubmit = () => {
    const data = new FormData();
    Object.entries(formData).forEach(([key, value]) => {
      if (key === 'instructions') {
        data.append(key, JSON.stringify(value));
      } else if (key === 'logo' && value) {
        data.append(key, value);
      } else {
        data.append(key, String(value));
      }
    });
    onSubmit(data);
  };

  const handleInstructionChange = (index: number, value: string) => {
    const newInstructions = [...formData.instructions];
    newInstructions[index] = value;
    setFormData({ ...formData, instructions: newInstructions });
  };

  const addInstruction = () => {
    setFormData({
      ...formData,
      instructions: [...formData.instructions, ''],
    });
  };

  const removeInstruction = (index: number) => {
    const newInstructions = formData.instructions.filter((_, i) => i !== index);
    setFormData({ ...formData, instructions: newInstructions });
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        {initialData ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran'}
      </DialogTitle>
      <DialogContent>
        <Stack spacing={3} sx={{ mt: 2 }}>
          <FormControl fullWidth>
            <InputLabel>Tipe Pembayaran</InputLabel>
            <Select
              value={formData.payment_type}
              label="Tipe Pembayaran"
              onChange={(e) => setFormData({
                ...formData,
                payment_type: e.target.value as 'bank_transfer' | 'e_wallet',
              })}
            >
              <MenuItem value="bank_transfer">Transfer Bank</MenuItem>
              <MenuItem value="e_wallet">E-Wallet</MenuItem>
            </Select>
          </FormControl>

          <TextField
            fullWidth
            label="Nama"
            value={formData.name}
            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
          />

          <TextField
            fullWidth
            label="Nomor Rekening/Akun"
            value={formData.account_number}
            onChange={(e) => setFormData({ ...formData, account_number: e.target.value })}
          />

          <TextField
            fullWidth
            label="Nama Pemilik"
            value={formData.account_name}
            onChange={(e) => setFormData({ ...formData, account_name: e.target.value })}
          />

          <TextField
            fullWidth
            label="Deskripsi"
            value={formData.description}
            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          />

          <FormControlLabel
            control={
              <Switch
                checked={formData.is_active}
                onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
              />
            }
            label="Aktif"
          />

          <input
            accept="image/*"
            style={{ display: 'none' }}
            id="logo-file"
            type="file"
            onChange={(e) => {
              const file = e.target.files?.[0];
              if (file) {
                setFormData({ ...formData, logo: file });
              }
            }}
          />
          <label htmlFor="logo-file">
            <Button variant="outlined" component="span">
              Upload Logo
            </Button>
          </label>

          <Box>
            <Typography variant="subtitle2" gutterBottom>
              Instruksi Pembayaran
            </Typography>
            {formData.instructions.map((instruction, index) => (
              <Box key={index} sx={{ display: 'flex', gap: 1, mb: 1 }}>
                <TextField
                  fullWidth
                  size="small"
                  value={instruction}
                  onChange={(e) => handleInstructionChange(index, e.target.value)}
                  placeholder={`Langkah ${index + 1}`}
                />
                <IconButton
                  size="small"
                  color="error"
                  onClick={() => removeInstruction(index)}
                  disabled={formData.instructions.length <= 1}
                >
                  <DeleteIcon />
                </IconButton>
              </Box>
            ))}
            <Button
              startIcon={<AddIcon />}
              onClick={addInstruction}
              size="small"
            >
              Tambah Langkah
            </Button>
          </Box>
        </Stack>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Batal</Button>
        <Button
          variant="contained"
          onClick={handleSubmit}
          disabled={loading}
        >
          {loading ? <CircularProgress size={24} /> : 'Simpan'}
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export const PaymentSettingsPage = () => {
  const [settings, setSettings] = useState<PaymentSetting[]>([]);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [selectedSetting, setSelectedSetting] = useState<PaymentSetting | undefined>();
  const [loading, setLoading] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetchSettings = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await api.getPaymentSettings();
      setSettings(response.data);
    } catch (err) {
      console.error(err);
      setError('Failed to load payment settings');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSettings();
  }, []);

  const handleSubmit = async (formData: FormData) => {
    try {
      setActionLoading(true);
      setError(null);

      if (selectedSetting) {
        await api.updatePaymentSetting(selectedSetting.id, formData);
      } else {
        await api.createPaymentSetting(formData);
      }

      await fetchSettings();
      setDialogOpen(false);
      setSelectedSetting(undefined);
    } catch (err: unknown) {
      console.error(err);
      if (err instanceof ApiError && err.errors) {
        // Show validation errors
        const errorMessages = Object.values(err.errors).flat().join('\n');
        setError(errorMessages || 'Failed to save payment setting');
      } else {
        setError('Failed to save payment setting');
      }
    } finally {
      setActionLoading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!window.confirm('Are you sure you want to delete this payment method?')) {
      return;
    }
    try {
      setActionLoading(true);
      await api.deletePaymentSetting(id);
      await fetchSettings();
    } catch (err) {
      console.error(err);
      setError('Failed to delete payment setting');
    } finally {
      setActionLoading(false);
    }
  };

  const handleToggleStatus = async (id: number) => {
    try {
      setActionLoading(true);
      await api.togglePaymentSettingStatus(id);
      await fetchSettings();
    } catch (err) {
      console.error(err);
      setError('Failed to toggle payment setting status');
    } finally {
      setActionLoading(false);
    }
  };

  return (
    <Box>
      <Box sx={{ mb: 4, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Typography variant="h4">
          Metode Pembayaran
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => {
            setSelectedSetting(undefined);
            setDialogOpen(true);
          }}
        >
          Tambah Metode
        </Button>
      </Box>

      {error && (
        <Alert severity="error" sx={{ mb: 2 }}>
          {error}
        </Alert>
      )}

      {loading ? (
        <Box sx={{ p: 3, textAlign: 'center' }}>
          <CircularProgress />
        </Box>
      ) : (
        <Grid container spacing={3}>
          {settings.map((setting) => (
            <Grid item xs={12} md={6} key={setting.id}>
              <Card>
                <CardContent>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                    {setting.payment_type === 'bank_transfer' ? (
                      <AccountBalance sx={{ mr: 1 }} />
                    ) : (
                      <AccountBalanceWallet sx={{ mr: 1 }} />
                    )}
                    <Typography variant="h6">
                      {setting.name}
                    </Typography>
                    <Box sx={{ ml: 'auto' }}>
                      <Chip
                        label={setting.is_active ? 'Aktif' : 'Nonaktif'}
                        color={setting.is_active ? 'success' : 'default'}
                        size="small"
                      />
                    </Box>
                  </Box>

                  <Stack spacing={1}>
                    <Typography variant="body2" color="text.secondary">
                      Nomor Rekening/Akun
                    </Typography>
                    <Typography>{setting.account_number}</Typography>

                    <Typography variant="body2" color="text.secondary">
                      Atas Nama
                    </Typography>
                    <Typography>{setting.account_name}</Typography>

                    {setting.description && (
                      <>
                        <Typography variant="body2" color="text.secondary">
                          Deskripsi
                        </Typography>
                        <Typography>{setting.description}</Typography>
                      </>
                    )}
                  </Stack>
                </CardContent>
                <CardActions>
                  <Button
                    size="small"
                    startIcon={<EditIcon />}
                    onClick={() => {
                      setSelectedSetting(setting);
                      setDialogOpen(true);
                    }}
                  >
                    Edit
                  </Button>
                  <Button
                    size="small"
                    color={setting.is_active ? 'error' : 'primary'}
                    onClick={() => handleToggleStatus(setting.id)}
                  >
                    {setting.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                  </Button>
                  <Button
                    size="small"
                    color="error"
                    startIcon={<DeleteIcon />}
                    onClick={() => handleDelete(setting.id)}
                  >
                    Hapus
                  </Button>
                </CardActions>
              </Card>
            </Grid>
          ))}
        </Grid>
      )}

      <PaymentSettingDialog
        open={dialogOpen}
        onClose={() => {
          setDialogOpen(false);
          setSelectedSetting(undefined);
        }}
        onSubmit={handleSubmit}
        initialData={selectedSetting}
        loading={actionLoading}
      />
    </Box>
  );
};

export default PaymentSettingsPage;
