import { useState, useEffect } from 'react';
import {
  Box,
  Card,
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
} from '@mui/material';
import {
  Search as SearchIcon,
} from '@mui/icons-material';
import { api } from '@/lib/api';
import { User, UserFilters } from '@/types/user';
import { useSnackbar } from 'notistack';

export default function UsersPage() {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [_, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState<UserFilters>({
    page: 1,
    per_page: 10,
    sort_field: 'created_at',
    sort_order: 'desc',
  });
  const [total, setTotal] = useState(0);
  const { enqueueSnackbar } = useSnackbar();

  useEffect(() => {
    fetchUsers();
    // Set up polling interval to check for new users
    const interval = setInterval(fetchUsers, 30000); // Poll every 30 seconds

    return () => clearInterval(interval); // Cleanup on unmount
  }, [filters]);

  const fetchUsers = async () => {
    try {
      setLoading(true);
      const response = await api.getUsers(filters);
      console.log('API Response:', response);
      
      if (response.data) {
        setUsers(response.data || []);
        setTotal(response.meta?.total || 0);
      } else {
        setUsers([]);
        setTotal(0);
      }
      setError(null);
    } catch (error) {
      console.error('Fetch users error:', error);
      setError('Failed to fetch users');
      enqueueSnackbar('Failed to fetch users', { variant: 'error' });
      setUsers([]);
      setTotal(0);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (event: React.ChangeEvent<HTMLInputElement>) => {
    setFilters({ ...filters, search: event.target.value, page: 1 });
  };

  const handlePageChange = (_: unknown, newPage: number) => {
    setFilters({ ...filters, page: newPage + 1 });
  };

  const handleRowsPerPageChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setFilters({ ...filters, per_page: parseInt(event.target.value, 10), page: 1 });
  };

  return (
    <Box>
      <Box sx={{ mb: 4 }}>
        <Typography variant="h4" component="h1">
          Manajemen Users
        </Typography>
      </Box>

      <Card sx={{ mb: 4 }}>
        <Box sx={{ p: 2 }}>
          <TextField
            fullWidth
            placeholder="Search users..."
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
                <TableCell>User</TableCell>
                <TableCell>Email</TableCell>
                <TableCell>Phone</TableCell>
                <TableCell>WhatsApp</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Registration Date</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell colSpan={6} align="center">
                    Loading...
                  </TableCell>
                </TableRow>
              ) : users.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} align="center">
                    No registered users found
                  </TableCell>
                </TableRow>
              ) : (
                users.map((user) => (
                  <TableRow key={user.id}>
                    <TableCell>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <Avatar src={user.avatar} alt={user.name} sx={{ mr: 2 }}>
                          {user.name.charAt(0)}
                        </Avatar>
                        <Typography>{user.name}</Typography>
                      </Box>
                    </TableCell>
                    <TableCell>{user.email}</TableCell>
                    <TableCell>{user.phone || '-'}</TableCell>
                    <TableCell>{user.whatsapp}</TableCell>
                    <TableCell>
                      <Chip
                        label={user.is_active ? 'Active' : 'Inactive'}
                        color={user.is_active ? 'success' : 'default'}
                      />
                    </TableCell>
                    <TableCell>
                      {new Date(user.created_at).toLocaleDateString()}
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
    </Box>
  );
}
