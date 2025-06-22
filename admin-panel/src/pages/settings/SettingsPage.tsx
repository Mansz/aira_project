import React, { useState, useEffect } from 'react';
import {
  Box,
  Paper,
  Typography,
  Tabs,
  Tab,
  TextField,
  Switch,
  Button,
  Alert,
  CircularProgress,
  FormControlLabel,
  Grid,
  Card,
  CardContent
} from '@mui/material';
import { api } from '../../lib/api';

interface Setting {
  id: number;
  key: string;
  value: any;
  type: string;
  group: string;
  description: string;
}

interface SettingsByGroup {
  [key: string]: Setting[];
}

interface TabPanelProps {
  children?: React.ReactNode;
  index: number;
  value: number;
}

function TabPanel(props: TabPanelProps) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`settings-tabpanel-${index}`}
      aria-labelledby={`settings-tab-${index}`}
      {...other}
    >
      {value === index && <Box sx={{ p: 3 }}>{children}</Box>}
    </div>
  );
}

export default function SettingsPage() {
  const [settings, setSettings] = useState<SettingsByGroup>({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [selectedTab, setSelectedTab] = useState(0);
  const [modifiedSettings, setModifiedSettings] = useState<{[key: string]: any}>({});

  const groups = ['general', 'payment', 'notification', 'shipping', 'whatsapp', 'streaming'];
  const groupLabels: {[key: string]: string} = {
    general: 'Umum',
    payment: 'Pembayaran',
    notification: 'Notifikasi',
    shipping: 'Pengiriman',
    whatsapp: 'WhatsApp',
    streaming: 'Live Streaming'
  };

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      setLoading(true);
      const response = await api.getSettings();
      setSettings(response.data);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch settings');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (key: string, value: any) => {
    setModifiedSettings(prev => ({
      ...prev,
      [key]: value
    }));
  };

  const handleSave = async () => {
    try {
      setLoading(true);
      const settingsToUpdate = Object.entries(modifiedSettings).map(([key, value]) => ({
        key,
        value
      }));

      await api.updateSettings({
        settings: settingsToUpdate
      });

      setSuccess('Settings berhasil disimpan');
      setModifiedSettings({});
      await fetchSettings();
    } catch (err: any) {
      setError(err.message || 'Failed to save settings');
    } finally {
      setLoading(false);
    }
  };

  const renderSettingInput = (setting: Setting) => {
    const value = modifiedSettings[setting.key] ?? setting.value;

    switch (setting.type) {
      case 'boolean':
        return (
          <FormControlLabel
            control={
              <Switch
                checked={Boolean(value)}
                onChange={(e) => handleChange(setting.key, e.target.checked)}
              />
            }
            label={setting.description}
          />
        );
      case 'number':
        return (
          <TextField
            fullWidth
            type="number"
            label={setting.description}
            value={value || ''}
            onChange={(e) => handleChange(setting.key, Number(e.target.value))}
            helperText={setting.description}
          />
        );
      default:
        return (
          <TextField
            fullWidth
            label={setting.description}
            value={value || ''}
            onChange={(e) => handleChange(setting.key, e.target.value)}
            helperText={setting.description}
            type={setting.key.includes('password') || setting.key.includes('key') ? 'password' : 'text'}
          />
        );
    }
  };

  if (loading && Object.keys(settings).length === 0) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', p: 3 }}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 3 }}>Pengaturan Sistem</Typography>

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

      <Paper sx={{ width: '100%', mb: 2 }}>
        <Tabs
          value={selectedTab}
          onChange={(_, newValue) => setSelectedTab(newValue)}
          variant="scrollable"
          scrollButtons="auto"
        >
          {groups.map((group, index) => (
            <Tab key={group} label={groupLabels[group]} id={`settings-tab-${index}`} />
          ))}
        </Tabs>

        {groups.map((group, index) => (
          <TabPanel key={group} value={selectedTab} index={index}>
            <Grid container spacing={3}>
              {settings[group]?.map((setting: Setting) => (
                <Grid item xs={12} md={6} key={setting.key}>
                  <Card>
                    <CardContent>
                      {renderSettingInput(setting)}
                    </CardContent>
                  </Card>
                </Grid>
              ))}
            </Grid>
          </TabPanel>
        ))}
      </Paper>

      <Box sx={{ display: 'flex', justifyContent: 'flex-end', mt: 2 }}>
        <Button
          variant="contained"
          onClick={handleSave}
          disabled={loading || Object.keys(modifiedSettings).length === 0}
          sx={{
            backgroundColor: 'black',
            '&:hover': {
              backgroundColor: '#333',
            },
          }}
        >
          {loading ? <CircularProgress size={24} /> : 'Simpan Pengaturan'}
        </Button>
      </Box>
    </Box>
  );
}
