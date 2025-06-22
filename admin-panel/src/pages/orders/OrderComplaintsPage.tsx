import React, { useEffect, useState } from 'react';
import {
  Box,
  Card,
  CardContent,
  Typography,
  Grid,
  Chip,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Stack,
} from '@mui/material';
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import { api } from '../../lib/api';
import { OrderComplaint, OrderComplaintStats } from '../../types/complaint';
import { formatDate } from '../../utils/format';
import { useSnackbar } from 'notistack';

export default function OrderComplaintsPage() {
  const [complaints, setComplaints] = useState<OrderComplaint[]>([]);
  const [stats, setStats] = useState<OrderComplaintStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedComplaint, setSelectedComplaint] = useState<OrderComplaint | null>(null);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [notes, setNotes] = useState('');
  const [action, setAction] = useState<'resolve' | 'reject' | null>(null);
  const { enqueueSnackbar } = useSnackbar();

  const fetchComplaints = async () => {
    try {
      const response = await api.getOrderComplaints();
      setComplaints(response.data);
    } catch (error) {
      console.error('Error fetching complaints:', error);
      enqueueSnackbar('Failed to fetch complaints', { variant: 'error' });
    }
  };

  const fetchStats = async () => {
    try {
      const response = await api.getOrderComplaintStats();
      setStats(response.data);
    } catch (error) {
      console.error('Error fetching stats:', error);
      enqueueSnackbar('Failed to fetch stats', { variant: 'error' });
    }
  };

  useEffect(() => {
    Promise.all([fetchComplaints(), fetchStats()]).finally(() => setLoading(false));
  }, []);

  const handleProcess = async (complaint: OrderComplaint) => {
    try {
      await api.processOrderComplaint(complaint.id);
      await fetchComplaints();
      enqueueSnackbar('Complaint marked as processing', { variant: 'success' });
    } catch (error) {
      console.error('Error processing complaint:', error);
      enqueueSnackbar('Failed to process complaint', { variant: 'error' });
    }
  };

  const handleAction = async () => {
    if (!selectedComplaint || !action) return;

    try {
      if (action === 'resolve') {
        await api.resolveOrderComplaint(selectedComplaint.id, notes);
      } else {
        await api.rejectOrderComplaint(selectedComplaint.id, notes);
      }
      await fetchComplaints();
      enqueueSnackbar(`Complaint ${action}d successfully`, { variant: 'success' });
      handleCloseDialog();
    } catch (error) {
      console.error('Error handling complaint:', error);
      enqueueSnackbar(`Failed to ${action} complaint`, { variant: 'error' });
    }
  };

  const handleOpenDialog = (complaint: OrderComplaint, actionType: 'resolve' | 'reject') => {
    setSelectedComplaint(complaint);
    setAction(actionType);
    setNotes('');
    setDialogOpen(true);
  };

  const handleCloseDialog = () => {
    setDialogOpen(false);
    setSelectedComplaint(null);
    setAction(null);
    setNotes('');
  };

  const columns: GridColDef[] = [
    { field: 'id', headerName: 'ID', width: 90 },
    { 
      field: 'order',
      headerName: 'Order',
      width: 130,
      valueGetter: (params) => params.row.order?.id || '-',
    },
    {
      field: 'description',
      headerName: 'Description',
      width: 300,
      flex: 1,
    },
    {
      field: 'status',
      headerName: 'Status',
      width: 150,
      renderCell: (params) => (
        <Chip
          label={params.row.status_label}
          sx={{ backgroundColor: params.row.status_color, color: 'white' }}
        />
      ),
    },
    {
      field: 'created_at',
      headerName: 'Created At',
      width: 180,
      valueGetter: (params) => formatDate(params.row.created_at),
    },
    {
      field: 'actions',
      headerName: 'Actions',
      width: 300,
      renderCell: (params) => {
        const complaint = params.row as OrderComplaint;
        return (
          <Stack direction="row" spacing={1}>
            {complaint.status === 'Pending' && (
              <Button
                variant="contained"
                color="primary"
                size="small"
                onClick={() => handleProcess(complaint)}
              >
                Process
              </Button>
            )}
            {['Pending', 'Processing'].includes(complaint.status) && (
              <>
                <Button
                  variant="contained"
                  color="success"
                  size="small"
                  onClick={() => handleOpenDialog(complaint, 'resolve')}
                >
                  Resolve
                </Button>
                <Button
                  variant="contained"
                  color="error"
                  size="small"
                  onClick={() => handleOpenDialog(complaint, 'reject')}
                >
                  Reject
                </Button>
              </>
            )}
          </Stack>
        );
      },
    },
  ];

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Order Complaints
      </Typography>

      {stats && (
        <Grid container spacing={2} sx={{ mb: 3 }}>
          <Grid item xs={12} sm={6} md={3}>
            <Card>
              <CardContent>
                <Typography color="textSecondary" gutterBottom>
                  Total Complaints
                </Typography>
                <Typography variant="h5">{stats.total}</Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={12} sm={6} md={3}>
            <Card>
              <CardContent>
                <Typography color="textSecondary" gutterBottom>
                  Pending
                </Typography>
                <Typography variant="h5" color="warning.main">
                  {stats.pending}
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={12} sm={6} md={3}>
            <Card>
              <CardContent>
                <Typography color="textSecondary" gutterBottom>
                  Processing
                </Typography>
                <Typography variant="h5" color="info.main">
                  {stats.processing}
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={12} sm={6} md={3}>
            <Card>
              <CardContent>
                <Typography color="textSecondary" gutterBottom>
                  Resolved
                </Typography>
                <Typography variant="h5" color="success.main">
                  {stats.resolved}
                </Typography>
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      )}

      <Card>
        <CardContent>
          <DataGrid
            rows={complaints}
            columns={columns}
            loading={loading}
            autoHeight
            disableRowSelectionOnClick
            initialState={{
              pagination: { paginationModel: { pageSize: 10 } },
              sorting: { sortModel: [{ field: 'created_at', sort: 'desc' }] },
            }}
            pageSizeOptions={[10, 25, 50]}
          />
        </CardContent>
      </Card>

      <Dialog open={dialogOpen} onClose={handleCloseDialog}>
        <DialogTitle>
          {action === 'resolve' ? 'Resolve Complaint' : 'Reject Complaint'}
        </DialogTitle>
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            label="Notes"
            fullWidth
            multiline
            rows={4}
            value={notes}
            onChange={(e) => setNotes(e.target.value)}
            required={action === 'reject'}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseDialog}>Cancel</Button>
          <Button 
            onClick={handleAction}
            variant="contained"
            color={action === 'resolve' ? 'success' : 'error'}
            disabled={action === 'reject' && !notes}
          >
            {action === 'resolve' ? 'Resolve' : 'Reject'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
