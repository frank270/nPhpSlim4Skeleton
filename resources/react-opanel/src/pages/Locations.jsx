import React, { useEffect, useMemo, useState, useRef } from 'react';
import ReactDOM from 'react-dom/client';
import { useTranslation } from 'react-i18next';
import '../i18n';

const initialFormState = {
  name: '',
  county: '',
  district: '',
  zipcode: '',
  address: '',
  phone: '',
  latitude: '',
  longitude: '',
  map_url: '',
  order_url: '',
  status: 'open',
  sort_order: 0,
  notes: '',
};

const toast = (message, type = 'success') => {
  if (window.showToast) {
    window.showToast(message, type);
  } else if (window.Tabler?.Toast) {
    window.Tabler.Toast.show(message, { color: type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue' });
  } else {
    // eslint-disable-next-line no-alert
    alert(message);
  }
};

function LocationsApp({ apiBase }) {
  const { t } = useTranslation();
  const [stores, setStores] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [refreshKey, setRefreshKey] = useState(0);

  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState(initialFormState);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [editingStore, setEditingStore] = useState(null);

  const [isDeleting, setIsDeleting] = useState(false);
  const [targetStore, setTargetStore] = useState(null);

  const addressSelectorRef = useRef(null);
  const addressContainerRef = useRef(null);

  const listEndpoint = useMemo(() => `${apiBase}/list`, [apiBase]);

  const loadStores = async () => {
    setLoading(true);
    setError('');
    try {
      const response = await fetch(listEndpoint);
      const result = await response.json();
      if (!result.success) {
        throw new Error(result.message || t('locations.error.load_failed'));
      }
      setStores(result.data || []);
    } catch (err) {
      setError(err.message);
      setStores([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadStores();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [listEndpoint, refreshKey]);

  // 初始化地址選擇器
  useEffect(() => {
    if (!showForm) {
      return;
    }

    // 等待 DOM 渲染完成
    const initAddressSelector = () => {
      const container = document.getElementById('address-selector-container');
      if (!container) {
        // 如果容器還不存在，等待一下再試
        setTimeout(initAddressSelector, 100);
        return;
      }

      // 確保 TaiwanAddressSelector 已載入
      if (typeof window.TaiwanAddressSelector === 'undefined') {
        console.warn('TaiwanAddressSelector 尚未載入，請確認已引入 /js/taiwan-address-selector.js');
        // 等待腳本載入
        setTimeout(initAddressSelector, 200);
        return;
      }

      // 如果已經初始化過，先清空容器
      if (addressSelectorRef.current) {
        container.innerHTML = '';
      }

      // 初始化地址選擇器
      try {
        addressSelectorRef.current = new window.TaiwanAddressSelector({
          containerId: 'address-selector-container',
          classes: {
            county: 'form-control',
            district: 'form-control',
            zipcode: 'form-control'
          },
          labels: {
            county: t('locations.county'),
            district: t('locations.district'),
            zipcode: t('locations.zipcode')
          },
          placeholders: {
            county: t('locations.placeholder.county'),
            district: t('locations.placeholder.district'),
            zipcode: t('locations.placeholder.zipcode')
          },
          values: {
            county: form.county || '',
            district: form.district || '',
            zipcode: form.zipcode || ''
          },
          onChange: (county, district, zipcode) => {
            // 更新表單狀態
            setForm(prev => ({
              ...prev,
              county: county || '',
              district: district || '',
              zipcode: zipcode || ''
            }));
          }
        });
      } catch (error) {
        console.error('初始化地址選擇器失敗:', error);
      }
    };

    // 稍微延遲確保 DOM 已渲染
    setTimeout(initAddressSelector, 100);

    return () => {
      // 清理
      addressSelectorRef.current = null;
      const container = document.getElementById('address-selector-container');
      if (container) {
        container.innerHTML = '';
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [showForm]);

  const handleInputChange = (event) => {
    const { name, value, type, checked } = event.target;
    if (type === 'checkbox') {
      setForm((prev) => ({ ...prev, [name]: checked }));
    } else {
      setForm((prev) => ({ ...prev, [name]: value }));
    }
  };

  const resetForm = () => {
    setForm(initialFormState);
    setShowForm(false);
    setEditingStore(null);
    // 重置地址選擇器
    if (addressSelectorRef.current) {
      addressSelectorRef.current.reset();
    }
  };

  const handleEdit = (store) => {
    setEditingStore(store);
    setForm({
      name: store.name || '',
      county: store.county || '',
      district: store.district || '',
      zipcode: store.zipcode || '',
      address: store.address || '',
      phone: store.phone || '',
      latitude: store.latitude || '',
      longitude: store.longitude || '',
      map_url: store.map_url || '',
      order_url: store.order_url || '',
      status: store.status || 'open',
      sort_order: store.sort_order || 0,
      notes: store.notes || '',
    });
    setShowForm(true);
    
    // 等待地址選擇器初始化後設定值
    setTimeout(() => {
      if (addressSelectorRef.current) {
        if (store.county) {
          addressSelectorRef.current.setCounty(store.county);
        }
        if (store.district) {
          setTimeout(() => {
            addressSelectorRef.current?.setDistrict(store.district);
          }, 100);
        }
        if (store.zipcode) {
          setTimeout(() => {
            addressSelectorRef.current?.setZipcode(store.zipcode);
          }, 200);
        }
      }
    }, 300);
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setIsSubmitting(true);
    try {
      if (!form.name.trim()) {
        throw new Error(t('locations.error.name_required'));
      }

      const payload = {
        name: form.name.trim(),
        county: form.county || null,
        district: form.district || null,
        zipcode: form.zipcode || null,
        address: form.address.trim() || null,
        phone: form.phone.trim() || null,
        latitude: form.latitude ? parseFloat(form.latitude) : null,
        longitude: form.longitude ? parseFloat(form.longitude) : null,
        map_url: form.map_url.trim() || null,
        order_url: form.order_url.trim() || null,
        status: form.status,
        sort_order: parseInt(form.sort_order) || 0,
        notes: form.notes.trim() || null,
      };

      const endpoint = editingStore 
        ? `${apiBase}/${editingStore.id}/edit`
        : `${apiBase}/create`;
      const method = editingStore ? 'POST' : 'POST';

      const response = await fetch(endpoint, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      let result;
      if (response.ok) {
        result = await response.json();
      } else {
        const text = await response.text();
        try {
          const json = JSON.parse(text);
          throw new Error(json.message || t('locations.error.save_failed'));
        } catch {
          throw new Error(text || t('locations.error.save_failed'));
        }
      }

      if (!result.success) {
        throw new Error(result.message || t('locations.error.save_failed'));
      }

      toast(editingStore ? t('common.saved') : t('common.saved'), 'success');
      resetForm();
      setRefreshKey((prev) => prev + 1);
    } catch (err) {
      toast(err.message || t('locations.error.save_failed'), 'error');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleToggleStatus = async (store) => {
    try {
      // 後端會自動循環切換狀態，所以不需要傳送 status
      const response = await fetch(`${apiBase}/${store.id}/toggle-status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({}),
      });

      let result;
      if (response.ok) {
        result = await response.json();
      } else {
        const text = await response.text();
        try {
          const json = JSON.parse(text);
          throw new Error(json.message || t('locations.error.status_failed'));
        } catch {
          throw new Error(text || t('locations.error.status_failed'));
        }
      }

      if (!result.success) {
        throw new Error(result.message || t('locations.error.status_failed'));
      }

      toast(t('common.status_updated'), 'success');
      // 更新列表中的狀態
      setStores((prev) => prev.map((item) => 
        item.id === store.id ? { ...item, status: result.data.status } : item
      ));
    } catch (err) {
      toast(err.message || t('locations.error.status_failed'), 'error');
    }
  };

  const handleDelete = async (store) => {
    if (!window.confirm(t('common.confirm_delete', { name: store.name }))) {
      return;
    }
    setTargetStore(store);
    setIsDeleting(true);
    try {
      const response = await fetch(`${apiBase}/${store.id}/delete`, {
        method: 'DELETE',
      });

      let result;
      if (response.ok) {
        result = await response.json();
      } else {
        const text = await response.text();
        try {
          const json = JSON.parse(text);
          throw new Error(json.message || t('locations.error.delete_failed'));
        } catch {
          throw new Error(text || t('locations.error.delete_failed'));
        }
      }

      if (!result.success) {
        throw new Error(result.message || t('locations.error.delete_failed'));
      }

      toast(t('common.deleted'), 'success');
      setStores((prev) => prev.filter((item) => item.id !== store.id));
    } catch (err) {
      toast(err.message || t('locations.error.delete_failed'), 'error');
    } finally {
      setIsDeleting(false);
      setTargetStore(null);
    }
  };

  const getStatusBadge = (status) => {
    const statusMap = {
      open: { label: t('locations.open'), class: 'bg-success text-white' },
      pause: { label: t('locations.pause'), class: 'bg-warning text-white' },
      closed: { label: t('locations.closed'), class: 'bg-danger text-white' },
    };
    const statusInfo = statusMap[status] || { label: status, class: 'bg-secondary text-white' };
    return <span className={`badge ${statusInfo.class}`}>{statusInfo.label}</span>;
  };

  const renderToolbar = () => (
    <div className="d-flex justify-content-between align-items-center mb-3">
      <h3 className="mb-0">{t('locations.title')}</h3>
      <div className="d-flex gap-2">
        <button
          className="btn btn-outline-secondary"
          type="button"
          onClick={() => setRefreshKey((prev) => prev + 1)}
          disabled={loading}
        >
          <span className="me-1" aria-hidden="true">⟳</span>
          {t('common.refresh')}
        </button>
        <button
          className="btn btn-primary"
          type="button"
          onClick={() => {
            resetForm();
            setShowForm((prev) => !prev);
          }}
        >
          {showForm ? t('locations.cancel_add') : t('locations.add_new')}
        </button>
      </div>
    </div>
  );

  const renderForm = () => {
    if (!showForm) return null;

    return (
      <div className="card mb-3">
        <div className="card-body">
          <h4 className="card-title mb-3">{editingStore ? t('locations.edit_store') : t('locations.add_new')}</h4>
          <form onSubmit={handleSubmit}>
            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label">{t('locations.name')}<span className="text-danger">*</span></label>
                <input
                  type="text"
                  className="form-control"
                  name="name"
                  value={form.name}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  required
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">{t('locations.phone')}</label>
                <input
                  type="text"
                  className="form-control"
                  name="phone"
                  value={form.phone}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  placeholder={t('locations.placeholder.phone')}
                />
              </div>
              <div className="col-12">
                <div id="address-selector-container" ref={addressContainerRef}></div>
              </div>
              <div className="col-12">
                <label className="form-label">{t('locations.address')}</label>
                <input
                  type="text"
                  className="form-control"
                  name="address"
                  value={form.address}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  placeholder={t('locations.placeholder.address')}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">{t('locations.latitude')}</label>
                <input
                  type="number"
                  step="any"
                  className="form-control"
                  name="latitude"
                  value={form.latitude}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  placeholder={t('locations.placeholder.latitude')}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">{t('locations.longitude')}</label>
                <input
                  type="number"
                  step="any"
                  className="form-control"
                  name="longitude"
                  value={form.longitude}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  placeholder={t('locations.placeholder.longitude')}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">{t('locations.map_url')}</label>
                <input
                  type="url"
                  className="form-control"
                  name="map_url"
                  value={form.map_url}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  placeholder="https://maps.google.com/..."
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">{t('locations.order_url')}</label>
                <input
                  type="url"
                  className="form-control"
                  name="order_url"
                  value={form.order_url}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  placeholder="https://www.yovvip.com/..."
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">{t('locations.status')}</label>
                <select
                  className="form-select"
                  name="status"
                  value={form.status}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                >
                  <option value="open">{t('locations.open')}</option>
                  <option value="pause">{t('locations.pause')}</option>
                  <option value="closed">{t('locations.closed')}</option>
                </select>
              </div>
              <div className="col-md-4">
                <label className="form-label">{t('locations.sort_order')}</label>
                <input
                  type="number"
                  className="form-control"
                  name="sort_order"
                  value={form.sort_order}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                />
              </div>
              <div className="col-12">
                <label className="form-label">{t('locations.notes')}</label>
                <textarea
                  className="form-control"
                  rows="3"
                  name="notes"
                  value={form.notes}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  placeholder={t('locations.placeholder.notes')}
                ></textarea>
              </div>
            </div>
            <div className="mt-4 d-flex gap-2">
              <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
                {isSubmitting ? t('common.loading') : (editingStore ? t('common.save') : t('locations.create_store'))}
              </button>
              <button type="button" className="btn btn-outline-secondary" onClick={resetForm} disabled={isSubmitting}>
                {t('common.cancel')}
              </button>
            </div>
          </form>
        </div>
      </div>
    );
  };

  const renderTable = () => {
    if (loading) {
      return (
        <div className="text-center py-5">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">{t('common.loading')}</span>
          </div>
        </div>
      );
    }

    if (error) {
      return <div className="alert alert-info">{t('locations.error.load_failed')}</div>;
    }

    if (!stores.length) {
      return <div className="alert alert-info">{t('locations.error.load_failed')}</div>;
    }

    return (
      <div className="table-responsive">
        <table className="table table-hover">
          <thead>
            <tr>
              <th>{t('locations.name')}</th>
              <th>{t('locations.county')}</th>
              <th>{t('locations.address')}</th>
              <th>{t('locations.phone')}</th>
              <th>{t('locations.status')}</th>
              <th>{t('locations.sort_order')}</th>
              <th className="text-end">{t('common.actions')}</th>
            </tr>
          </thead>
          <tbody>
            {stores.map((store) => (
              <tr key={store.id}>
                <td>{store.name}</td>
                <td>{store.county || '-'}</td>
                <td>{store.address || '-'}</td>
                <td>{store.phone || '-'}</td>
                <td>{getStatusBadge(store.status)}</td>
                <td>{store.sort_order}</td>
                <td className="text-end">
                  <div className="btn-group">
                    <button
                      type="button"
                      className="btn btn-sm btn-ghost-primary"
                      onClick={() => handleEdit(store)}
                    >
                      {t('common.edit')}
                    </button>
                    <button
                      type="button"
                      className="btn btn-sm btn-ghost-warning"
                      onClick={() => handleToggleStatus(store)}
                    >
                      {t('locations.toggle_status')}
                    </button>
                    <button
                      type="button"
                      className="btn btn-sm btn-ghost-danger"
                      onClick={() => handleDelete(store)}
                      disabled={isDeleting && targetStore?.id === store.id}
                    >
                      {t('common.delete')}
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    );
  };

  return (
    <div className="container-xl mt-3">
      {renderToolbar()}
      {renderForm()}
      <div className="card">
        <div className="card-body">
          {renderTable()}
        </div>
      </div>
    </div>
  );
}

const mountNode = document.getElementById('locations-app');

if (mountNode) {
  const apiBase = mountNode.dataset.apiBase || '/opanel/locations';
  ReactDOM.createRoot(mountNode).render(
    <React.StrictMode>
      <LocationsApp apiBase={apiBase} />
    </React.StrictMode>
  );
}

