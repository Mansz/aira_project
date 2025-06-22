import { useState, useEffect } from 'react';
import {
  Box,
  Button,
  Card,
  IconButton,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TablePagination,
  TextField,
  Typography,
  Avatar,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  FormControlLabel,
  Switch,
  Stack,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
  ListItemText,
  Checkbox,
  OutlinedInput,
} from '@mui/material';
import {
  Add as AddIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Search as SearchIcon,
} from '@mui/icons-material';
import { api } from '@/lib/api';
import { Admin, AdminFilters, AdminFormData, Permission } from '@/types/admin';
import { useSnackbar } from 'notistack';

const ROLES = [
  { value: 'super_admin', label: 'Super Admin' },
  { value: 'admin', label: 'Admin' },
  { value: 'moderator', label: 'Moderator' },
];

export default function AdminsPage() {
  const [admins, setAdmins] = useState<Admin[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState<AdminFilters>({
    page: 1,
    per_page: 10,
    sort_field: 'created_at',
    sort_order: 'desc',
  });
  const [total, setTotal] = useState(0);
  const [openDialog, setOpenDialog] = useState(false);
  const [selectedAdmin, setSelectedAdmin] = useState<Admin | null>(null);
  const [formData, setFormData] = useState<AdminFormData>({
    name: '',
    email: '',
    role: 'admin',
    permissions: [],
    is_active: true,
  });
  const [permissions, setPermissions] = useState<Permission[]>([]);
  const { enqueueSnackbar } = useSnackbar();

  useEffect(() => {
    fetchAdmins();
    fetchPermissions();
  }, [filters]);

  const fetchAdmins = async () => {
    try {
      setLoading(true);
      const response = await api.getAdmins(filters);
      console.log('API Response:', response);
      
      // Handle different response structures
      if (response.data) {
        // Check if response.data is an array or has a data property
        if (Array.isArray(response.data)) {
          setAdmins(response.data);
          setTotal(response.data.length);
        } else if (response.data.data && Array.isArray(response.data.data)) {
          setAdmins(response.data.data);
          setTotal(response.data.meta?.total || response.data.data.length);
        } else {
          setAdmins([]);
          setTotal(0);
        }
      } else {
        setAdmins([]);
        setTotal(0);
      }
      setError(null);
    } catch (err) {
      console.error('Fetch admins error:', err);
      setError('Failed to fetch admins');
      enqueueSnackbar('Failed to fetch admins', { variant: 'error' });
      setAdmins([]); // Set empty array on error
      setTotal(0);
    } finally {
      setLoading(false);
    }
  };

  const fetchPermissions = async () => {
    try {
      const response = await api.getAdminPermissions();
      // Convert permissions object to array of permission objects
      if (response.data?.permissions) {
        const permissionArray = Object.entries(response.data.permissions).map(([key, name]) => ({
          key,
          name: name as string,
          description: '' // Add description if available in your API
        }));
        setPermissions(permissionArray);
      } else {
        setPermissions([]);
      }
    } catch (err) {
      console.error('Fetch permissions error:', err);
      enqueueSnackbar('Failed to fetch permissions', { variant: 'error' });
      setPermissions([]);
    }
  };

  const handleSearch = (event: React.ChangeEvent<HTMLInputElement>) => {
    setFilters({ ...filters, search: event.target.value, page: 1 });
  };

  const handlePageChange = (event: unknown, newPage: number) => {
    setFilters({ ...filters, page: newPage + 1 });
  };

  const handleRowsPerPageChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setFilters({ ...filters, per_page: parseInt(event.target.value, 10), page: 1 });
  };

  const handleOpenDialog = (admin?: Admin) => {
    if (admin) {
      setSelectedAdmin(admin);
      setFormData({
        name: admin.name,
        email: admin.email,
        role: admin.role,
        permissions: Array.isArray(admin.permissions) ? admin.permissions : [],
        is_active: admin.is_active,
      });
    } else {
      setSelectedAdmin(null);
      setFormData({
        name: '',
        email: '',
        role: 'admin',
        permissions: [],
        is_active: true,
      });
    }
    setOpenDialog(true);
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
    setSelectedAdmin(null);
    setFormData({
      name: '',
      email: '',
      role: 'admin',
      permissions: [],
      is_active: true,
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      if (selectedAdmin) {
        await api.updateAdmin(selectedAdmin.id, formData);
        enqueueSnackbar('Admin updated successfully', { variant: 'success' });
      } else {
        await api.createAdmin(formData);
        enqueueSnackbar('Admin created successfully', { variant: 'success' });
      }
      handleCloseDialog();
      fetchAdmins();
    } catch (err) {
      enqueueSnackbar('Operation failed', { variant: 'error' });
    }
  };

  const handleDelete = async (admin: Admin) => {
    if (window.confirm('Are you sure you want to delete this admin?')) {
      try {
        await api.deleteAdmin(admin.id);
        enqueueSnackbar('Admin deleted successfully', { variant: 'success' });
        fetchAdmins();
      } catch (err) {
        enqueueSnackbar('Failed to delete admin', { variant: 'error' });
      }
    }
  };

  const handleToggleStatus = async (admin: Admin) => {
    try {
      await api.toggleAdminStatus(admin.id);
      enqueueSnackbar('Admin status updated successfully', { variant: 'success' });
      fetchAdmins();
    } catch (err) {
      enqueueSnackbar('Failed to update admin status', { variant: 'error' });
    }
  };

  return (
    <Box>
      <Box sx={{ mb: 4, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Typography variant="h4" component="h1">
          Admins
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => handleOpenDialog()}
        >
          Add Admin
        </Button>
      </Box>

      <Card sx={{ mb: 4 }}>
        <Box sx={{ p: 2 }}>
          <TextField
            fullWidth
            placeholder="Search admins..."
            InputProps={{
              startAdornment: <SearchIcon sx={{ mr: 1, color: 'text.secondary' }} />,
            }}
            onChange={handleSearch}
          />
        </Box>

        <TableContainer>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Admin</TableCell>
                <TableCell>Email</TableCell>
                <TableCell>Role</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Last Login</TableCell>
                <TableCell align="right">Actions</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell colSpan={6} align="center">
                    Loading...
                  </TableCell>
                </TableRow>
              ) : admins.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} align="center">
                    No admins found
                  </TableCell>
                </TableRow>
              ) : (
                admins.map((admin) => (
                  <TableRow key={admin.id}>
                    <TableCell>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <Avatar src={admin.avatar} alt={admin.name} sx={{ mr: 2 }}>
                          {admin.name.charAt(0)}
                        </Avatar>
                        <Typography>{admin.name}</Typography>
                      </Box>
                    </TableCell>
                    <TableCell>{admin.email}</TableCell>
                    <TableCell>
                      <Chip
                        label={admin.role.replace('_', ' ').toUpperCase()}
                        color={admin.role === 'super_admin' ? 'error' : admin.role === 'admin' ? 'primary' : 'default'}
                      />
                    </TableCell>
                    <TableCell>
                      <Chip
                        label={admin.is_active ? 'Active' : 'Inactive'}
                        color={admin.is_active ? 'success' : 'default'}
                        onClick={() => handleToggleStatus(admin)}
                      />
                    </TableCell>
                    <TableCell>
                      {admin.last_login_at ? new Date(admin.last_login_at).toLocaleDateString() : 'Never'}
                    </TableCell>
                    <TableCell align="right">
                      <IconButton onClick={() => handleOpenDialog(admin)}>
                        <EditIcon />
                      </IconButton>
                      <IconButton onClick={() => handleDelete(admin)}>
                        <DeleteIcon />
                      </IconButton>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </TableContainer>

        <TablePagination
          component="div"
          count={total}
          page={filters.page ? filters.page - 1 : 0}
          onPageChange={handlePageChange}
          rowsPerPage={filters.per_page || 10}
          onRowsPerPageChange={handleRowsPerPageChange}
        />
      </Card>

      <Dialog open={openDialog} onClose={handleCloseDialog} maxWidth="sm" fullWidth>
        <form onSubmit={handleSubmit}>
          <DialogTitle>
            {selectedAdmin ? 'Edit Admin' : 'Add Admin'}
          </DialogTitle>
          <DialogContent>
            <Stack spacing={3} sx={{ mt: 2 }}>
              <TextField
                label="Name"
                fullWidth
                required
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              />
              <TextField
                label="Email"
                type="email"
                fullWidth
                required
                value={formData.email}
                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              />
              {!selectedAdmin && (
                <TextField
                  label="Password"
                  type="password"
                  fullWidth
                  required={!selectedAdmin}
                  value={formData.password || ''}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                />
              )}
              <FormControl fullWidth>
                <InputLabel>Role</InputLabel>
                <Select
                  value={formData.role}
                  label="Role"
                  onChange={(e) => setFormData({ ...formData, role: e.target.value as any })}
                >
                  {ROLES.map((role) => (
                    <MenuItem key={role.value} value={role.value}>
                      {role.label}
                    </MenuItem>
                  ))}
                </Select>
              </FormControl>
              <FormControl fullWidth>
                <InputLabel>Permissions</InputLabel>
                <Select
                  multiple
                  value={Array.isArray(formData.permissions) ? formData.permissions : []}
                  onChange={(e) => setFormData({ ...formData, permissions: e.target.value as string[] })}
                  input={<OutlinedInput label="Permissions" />}
                  renderValue={(selected) => Array.isArray(selected) ? selected.join(', ') : ''}
                >
                  {permissions.map((permission) => (
                    <MenuItem key={permission.key} value={permission.key}>
                      <Checkbox checked={Array.isArray(formData.permissions) && formData.permissions.indexOf(permission.key) > -1} />
                      <ListItemText primary={permission.name} secondary={permission.description} />
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
                label="Active"
              />
            </Stack>
          </DialogContent>
          <DialogActions>
            <Button onClick={handleCloseDialog}>Cancel</Button>
            <Button type="submit" variant="contained">
              {selectedAdmin ? 'Update' : 'Create'}
            </Button>
          </DialogActions>
        </form>
      </Dialog>
    </Box>
  );
}
