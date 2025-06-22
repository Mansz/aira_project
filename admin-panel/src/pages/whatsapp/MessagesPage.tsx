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
  Chip,
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
} from '@mui/material';
import { Send as SendIcon, Refresh as RefreshIcon } from '@mui/icons-material';
import { api } from '../../lib/api';
import { WhatsAppMessage } from '../../types/whatsapp';
import { format } from 'date-fns';

const statusColors = {
  pending: 'warning',
  sent: 'info',
  delivered: 'success',
  read: 'success',
  failed: 'error',
};

export default function MessagesPage() {
  const [messages, setMessages] = useState<WhatsAppMessage[]>([]);
  const [loading, setLoading] = useState(false);
  const [openSendDialog, setOpenSendDialog] = useState(false);
  const [newMessage, setNewMessage] = useState({
    phone_number: '',
    message: '',
  });

  const fetchMessages = async () => {
    setLoading(true);
    try {
      const response = await api.getWhatsAppMessages();
      setMessages(response.data);
    } catch (error) {
      console.error('Error fetching messages:', error);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchMessages();
  }, []);

  const handleSendMessage = async () => {
    try {
      await api.sendWhatsAppMessage(newMessage);
      setOpenSendDialog(false);
      setNewMessage({ phone_number: '', message: '' });
      fetchMessages();
    } catch (error) {
      console.error('Error sending message:', error);
    }
  };

  const getStatusChipColor = (status: string) => {
    return statusColors[status as keyof typeof statusColors] || 'default';
  };

  return (
    <Box p={3}>
      <Grid container spacing={3}>
        <Grid item xs={12}>
          <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
            <Typography variant="h4">WhatsApp Messages</Typography>
            <Box>
              <Button
                variant="contained"
                color="primary"
                startIcon={<SendIcon />}
                onClick={() => setOpenSendDialog(true)}
                sx={{ mr: 1 }}
              >
                Send Message
              </Button>
              <IconButton onClick={fetchMessages} disabled={loading}>
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
                      <TableCell>Phone Number</TableCell>
                      <TableCell>Message</TableCell>
                      <TableCell>Direction</TableCell>
                      <TableCell>Status</TableCell>
                      <TableCell>Date</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {messages.map((message) => (
                      <TableRow key={message.id}>
                        <TableCell>{message.phone_number}</TableCell>
                        <TableCell>{message.message}</TableCell>
                        <TableCell>
                          <Chip
                            label={message.direction}
                            color={message.direction === 'inbound' ? 'success' : 'primary'}
                            size="small"
                          />
                        </TableCell>
                        <TableCell>
                          <Chip
                            label={message.status}
                            color={getStatusChipColor(message.status) as any}
                            size="small"
                          />
                        </TableCell>
                        <TableCell>
                          {format(new Date(message.created_at), 'dd MMM yyyy HH:mm')}
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

      {/* Send Message Dialog */}
      <Dialog open={openSendDialog} onClose={() => setOpenSendDialog(false)} maxWidth="sm" fullWidth>
        <DialogTitle>Send WhatsApp Message</DialogTitle>
        <DialogContent>
          <Box mt={2}>
            <TextField
              fullWidth
              label="Phone Number"
              value={newMessage.phone_number}
              onChange={(e) => setNewMessage({ ...newMessage, phone_number: e.target.value })}
              placeholder="e.g., +6281234567890"
              margin="normal"
            />
            <TextField
              fullWidth
              label="Message"
              value={newMessage.message}
              onChange={(e) => setNewMessage({ ...newMessage, message: e.target.value })}
              multiline
              rows={4}
              margin="normal"
            />
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setOpenSendDialog(false)}>Cancel</Button>
          <Button
            onClick={handleSendMessage}
            variant="contained"
            color="primary"
            disabled={!newMessage.phone_number || !newMessage.message}
          >
            Send
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
