import React, { useState, useEffect } from 'react';
import {
  Box,
  Button,
  Card,
  CardContent,
  Grid,
  TextField,
  Typography,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Switch,
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  FormControlLabel,
  Checkbox,
} from '@mui/material';
import {
  Add as AddIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Refresh as RefreshIcon,
} from '@mui/icons-material';
import { api } from '../../lib/api';
import { WhatsAppAutoReply } from '../../types/whatsapp';
import { format } from 'date-fns';

export default function AutoRepliesPage() {
  const [autoReplies, setAutoReplies] = useState<WhatsAppAutoReply[]>([]);
  const [loading, setLoading] = useState(false);
  const [openDialog, setOpenDialog] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [formData, setFormData] = useState({
    keyword: '',
    response: '',
    is_regex: false,
    is_active: true,
  });

  const fetchAutoReplies = async () => {
    setLoading(true);
    try {
      const response = await api.getWhatsAppAutoReplies();
      setAutoReplies(response.data);
    } catch (error) {
      console.error('Error fetching auto-replies:', error);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchAutoReplies();
  }, []);

  const handleOpenDialog = (autoReply?: WhatsAppAutoReply) => {
    if (autoReply) {
      setEditingId(autoReply.id);
      setFormData({
        keyword: autoReply.keyword,
        response: autoReply.response,
        is_regex: autoReply.is_regex,
        is_active: autoReply.is_active,
      });
    } else {
      setEditingId(null);
      setFormData({
        keyword: '',
        response: '',
        is_regex: false,
        is_active: true,
      });
    }
    setOpenDialog(true);
  };

  const handleSubmit = async () => {
    try {
      if (editingId) {
        await api.updateWhatsAppAutoReply(editingId, formData);
      } else {
        await api.createWhatsAppAutoReply(formData);
      }
      setOpenDialog(false);
      fetchAutoReplies();
    } catch (error) {
      console.error('Error saving auto-reply:', error);
    }
  };

  const handleDelete = async (id: number) => {
    if (window.confirm('Are you sure you want to delete this auto-reply?')) {
      try {
        await api.deleteWhatsAppAutoReply(id);
        fetchAutoReplies();
      } catch (error) {
        console.error('Error deleting auto-reply:', error);
      }
    }
  };

  const handleToggleStatus = async (id: number) => {
    try {
      await api.toggleWhatsAppAutoReply(id);
      fetchAutoReplies();
    } catch (error) {
      console.error('Error toggling auto-reply status:', error);
    }
  };

  return (
    <Box p={3}>
      <Grid container spacing={3}>
        <Grid item xs={12}>
          <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
            <Typography variant="h4">WhatsApp Auto Replies</Typography>
            <Box>
              <Button
                variant="contained"
                color="primary"
                startIcon={<AddIcon />}
                onClick={() => handleOpenDialog()}
                sx={{ mr: 1 }}
              >
                Add Auto Reply
              </Button>
              <IconButton onClick={fetchAutoReplies} disabled={loading}>
                <RefreshIcon />
              </IconButton>
            </Box>
          </Box>
        </Grid>

        <Grid item xs={12}>
          <Card>
            <CardContent>
              <TableContainer component={Paper}>
                <Table>
                  <TableHead>
                    <TableRow>
                      <TableCell>Keyword</TableCell>
                      <TableCell>Response</TableCell>
                      <TableCell>Regex</TableCell>
                      <TableCell>Status</TableCell>
                      <TableCell>Last Updated</TableCell>
                      <TableCell>Actions</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {autoReplies.map((autoReply) => (
                      <TableRow key={autoReply.id}>
                        <TableCell>{autoReply.keyword}</TableCell>
                        <TableCell>{autoReply.response}</TableCell>
                        <TableCell>
                          <Checkbox checked={autoReply.is_regex} disabled />
                        </TableCell>
                        <TableCell>
                          <Switch
                            checked={autoReply.is_active}
                            onChange={() => handleToggleStatus(autoReply.id)}
                          />
                        </TableCell>
                        <TableCell>
                          {format(new Date(autoReply.updated_at), 'dd MMM yyyy HH:mm')}
                        </TableCell>
                        <TableCell>
                          <IconButton
                            color="primary"
                            onClick={() => handleOpenDialog(autoReply)}
                            size="small"
                          >
                            <EditIcon />
                          </IconButton>
                          <IconButton
                            color="error"
                            onClick={() => handleDelete(autoReply.id)}
                            size="small"
                          >
                            <DeleteIcon />
                          </IconButton>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Add/Edit Dialog */}
      <Dialog open={openDialog} onClose={() => setOpenDialog(false)} maxWidth="sm" fullWidth>
        <DialogTitle>{editingId ? 'Edit Auto Reply' : 'Add Auto Reply'}</DialogTitle>
        <DialogContent>
          <Box mt={2}>
            <TextField
              fullWidth
              label="Keyword"
              value={formData.keyword}
              onChange={(e) => setFormData({ ...formData, keyword: e.target.value })}
              margin="normal"
            />
            <TextField
              fullWidth
              label="Response"
              value={formData.response}
              onChange={(e) => setFormData({ ...formData, response: e.target.value })}
              multiline
              rows={4}
              margin="normal"
            />
            <FormControlLabel
              control={
                <Checkbox
                  checked={formData.is_regex}
                  onChange={(e) => setFormData({ ...formData, is_regex: e.target.checked })}
                />
              }
              label="Use Regular Expression"
            />
            <FormControlLabel
              control={
                <Checkbox
                  checked={formData.is_active}
                  onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                />
              }
              label="Active"
            />
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setOpenDialog(false)}>Cancel</Button>
          <Button
            onClick={handleSubmit}
            variant="contained"
            color="primary"
            disabled={!formData.keyword || !formData.response}
          >
            {editingId ? 'Update' : 'Create'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
