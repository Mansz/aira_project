import React, { useState, useEffect } from 'react';
import {
  Box,
  Paper,
  Typography,
  Button,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  IconButton,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  FormControlLabel,
  Switch,
  Alert,
  CircularProgress,
  Stack,
  Avatar,
} from '@mui/material';
import { Edit as EditIcon, Delete as DeleteIcon } from '@mui/icons-material';
import { api, Admin } from '../../lib/api';

interface AdminFormData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role: string;
  permissions: string[];
  is_active: boolean;
}

export default function UsersPage() {
  const [admins, setAdmins] = useState<Admin[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [openDialog, setOpenDialog] = useState(false);
  const [editingAdmin, setEditingAdmin] = useState<Admin | null>(null);
  const [formData, setFormData] = useState<AdminFormData>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'staff',
    permissions: [],
    is_active: true,
  });
  const [roles, setRoles] = useState<Record<string, string>>({});
  const [permissions, setPermissions] = useState<Record<string, string>>({});

  useEffect(() => {
    fetchAdmins();
    fetchPermissions();
  }, []);

  const fetchAdmins = async () => {
    try {
      setLoading(true);
      const response = await api.getAdmins();
      setAdmins(response.data.data);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const fetchPermissions = async () => {
    try {
      const response = await api.getAdminPermissions();
      setRoles(response.data.data.roles);
      setPermissions(response.data.data.permissions);
    } catch (err: any) {
      setError(err.message);
    }
  };

  const handleOpenDialog = (admin?: Admin) => {
    if (admin) {
      setEditingAdmin(admin);
      setFormData({
        name: admin.name,
        email: admin.email,
        password: '',
        password_confirmation: '',
        role: admin.role,
        permissions: admin.permissions,
        is_active: admin.is_active,
      });
    } else {
      setEditingAdmin(null);
      setFormData({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'staff',
        permissions: [],
        is_active: true,
      });
    }
    setOpenDialog(true);
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
    setEditingAdmin(null);
    setFormData({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: 'staff',
      permissions: [],
      is_active: true,
    });
  };

  const handleSubmit = async () => {
    try {
      setLoading(true);
      if (editingAdmin) {
        await api.updateAdmin(editingAdmin.id, formData);
      } else {
        await api.createAdmin(formData);
      }
      setSuccess(editingAdmin ? 'Admin berhasil diperbarui' : 'Admin berhasil dibuat');
      handleCloseDialog();
      await fetchAdmins();
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (admin: Admin) => {
    if (!window.confirm('Apakah Anda yakin ingin menghapus admin ini?')) {
      return;
    }

    try {
      setLoading(true);
      await api.deleteAdmin(admin.id);
      setSuccess('Admin berhasil dihapus');
      await fetchAdmins();
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleToggleStatus = async (admin: Admin) => {
    try {
      setLoading(true);
      await api.toggleAdminStatus(admin.id);
      setSuccess(`Admin berhasil ${admin.is_active ? 'dinonaktifkan' : 'diaktifkan'}`);
      await fetchAdmins();
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  if (loading && admins.length === 0) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', p: 3 }}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4">Manajemen Admin</Typography>
        <Button
          variant="contained"
          onClick={() => handleOpenDialog()}
          sx={{
            backgroundColor: 'black',
            '&:hover': {
              backgroundColor: '#333',
            },
          }}
        >
          Tambah Admin
        </Button>
      </Stack>

      {error && (
        <Alert severity="error" sx={{ mb: 2 }}>
          {error}
        </Alert>
      )}

      {success && (
        <Alert severity="success" sx={{ mb: 2 }}>
          {success}
        </Alert>
      )}

      <TableContainer component={Paper}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell>Admin</TableCell>
              <TableCell>Email</TableCell>
              <TableCell>Role</TableCell>
              <TableCell>Status</TableCell>
              <TableCell>Login Terakhir</TableCell>
              <TableCell align="right">Aksi</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {admins.map((admin) => (
              <TableRow key={admin.id}>
                <TableCell>
                  <Stack direction="row" spacing={2} alignItems="center">
                    <Avatar src={admin.avatar_url} alt={admin.name} />
                    <Typography>{admin.name}</Typography>
                  </Stack>
                </TableCell>
                <TableCell>{admin.email}</TableCell>
                <TableCell>
                  <Chip
                    label={roles[admin.role] || admin.role}
                    color={admin.role === 'super_admin' ? 'error' : 'default'}
                  />
                </TableCell>
                <TableCell>
                  <Chip
                    label={admin.is_active ? 'Aktif' : 'Nonaktif'}
                    color={admin.is_active ? 'success' : 'default'}
                  />
                </TableCell>
                <TableCell>
                  {admin.last_login_at ? new Date(admin.last_login_at).toLocaleString() : '-'}
                </TableCell>
                <TableCell align="right">
                  <IconButton onClick={() => handleOpenDialog(admin)} color="primary">
                    <EditIcon />
                  </IconButton>
                  <IconButton onClick={() => handleDelete(admin)} color="error">
                    <DeleteIcon />
                  </IconButton>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>

      <Dialog open={openDialog} onClose={handleCloseDialog} maxWidth="sm" fullWidth>
        <DialogTitle>{editingAdmin ? 'Edit Admin' : 'Tambah Admin'}</DialogTitle>
        <DialogContent>
          <Box sx={{ mt: 2 }}>
            <TextField
              fullWidth
              label="Nama"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              sx={{ mb: 2 }}
            />
            <TextField
              fullWidth
              label="Email"
              type="email"
              value={formData.email}
              onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              sx={{ mb: 2 }}
            />
            {!editingAdmin && (
              <>
                <TextField
                  fullWidth
                  label="Password"
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  sx={{ mb: 2 }}
                />
                <TextField
                  fullWidth
                  label="Konfirmasi Password"
                  type="password"
                  value={formData.password_confirmation}
                  onChange={(e) =>
                    setFormData({ ...formData, password_confirmation: e.target.value })
                  }
                  sx={{ mb: 2 }}
                />
              </>
            )}
            <FormControl fullWidth sx={{ mb: 2 }}>
              <InputLabel>Role</InputLabel>
              <Select
                value={formData.role}
                onChange={(e) => setFormData({ ...formData, role: e.target.value })}
                label="Role"
              >
                {Object.entries(roles).map(([value, label]) => (
                  <MenuItem key={value} value={value}>
                    {label}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
            <FormControl fullWidth sx={{ mb: 2 }}>
              <InputLabel>Permissions</InputLabel>
              <Select
                multiple
                value={formData.permissions}
                onChange={(e) => {
                  const value = e.target.value;
                  setFormData({
                    ...formData,
                    permissions: typeof value === 'string' ? value.split(',') : value,
                  });
                }}
                label="Permissions"
              >
                {Object.entries(permissions).map(([value, label]) => (
                  <MenuItem key={value} value={value}>
                    {label}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
            <FormControlLabel
              control={
                <Switch
                  checked={formData.is_active}
                  onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                />
              }
              label="Aktif"
            />
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseDialog}>Batal</Button>
          <Button
            onClick={handleSubmit}
            variant="contained"
            disabled={loading}
            sx={{
              backgroundColor: 'black',
              '&:hover': {
                backgroundColor: '#333',
              },
            }}
          >
            {loading ? <CircularProgress size={24} /> : editingAdmin ? 'Simpan' : 'Tambah'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
