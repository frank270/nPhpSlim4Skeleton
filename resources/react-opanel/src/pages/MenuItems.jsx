import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom/client';
import { useTranslation } from 'react-i18next';
import '../i18n';

const initialForm = {
  name: '',
  description: '',
  category_id: '',
  price_original: '',
  price_sale: '',
  media_id: '',
  tags: '',
  is_best_seller: 0,
  status: 'draft',
};

const toast = (message, type = 'success') => {
    if (window.showToast) {
        window.showToast(message, type);
    } else if (window.Tabler?.Toast) {
        window.Tabler.Toast.show(message, { color: type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue' });
    } else {
        alert(message);
    }
};

function MenuItemsApp({ apiBase, apiCategoryBase }) {
  const { t } = useTranslation();
  const [items, setItems] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState(initialForm);
  const [editingId, setEditingId] = useState(null);
  const [submitting, setSubmitting] = useState(false);
  
  // Filters
  const [filterCategory, setFilterCategory] = useState('');

  const loadItems = async () => {
    setLoading(true);
    try {
      let url = `${apiBase}/list?`;
      if (filterCategory) url += `category_id=${filterCategory}&`;
      
      const res = await fetch(url);
      const data = await res.json();
      if (data.success) {
        setItems(data.data);
      } else {
        toast(data.message || '載入失敗', 'error');
      }
    } catch (e) {
      toast(e.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  const loadCategories = async () => {
    try {
      const res = await fetch(`${apiCategoryBase}/all`);
      const data = await res.json();
      if (data.success) {
        setCategories(data.data);
      }
    } catch (e) {
      console.error(e);
    }
  };

  useEffect(() => {
    loadCategories();
  }, [apiCategoryBase]);

  useEffect(() => {
    loadItems();
  }, [apiBase, filterCategory]);

  const handleEdit = (item) => {
    setForm({
      name: item.name,
      description: item.description || '',
      category_id: item.category_id,
      price_original: item.price_original,
      price_sale: item.price_sale || '',
      media_id: item.media_id || '',
      tags: item.tags || '',
      is_best_seller: item.is_best_seller,
      status: item.status,
    });
    setEditingId(item.id);
    setShowForm(true);
  };

  const handleDelete = async (id) => {
    if (!confirm('確定要刪除嗎？')) return;
    try {
      const res = await fetch(`${apiBase}/${id}/delete`, { method: 'DELETE' });
      const data = await res.json();
      if (data.success) {
        toast('刪除成功');
        loadItems();
      } else {
        toast(data.message || '刪除失敗', 'error');
      }
    } catch (e) {
      toast(e.message, 'error');
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      const url = editingId 
        ? `${apiBase}/${editingId}/edit`
        : `${apiBase}/create`;
      
      // Prepare payload
      const payload = { ...form };
      if (!payload.price_sale) payload.price_sale = ''; // Send empty string if null
      if (!payload.media_id) payload.media_id = '';

      const formData = new URLSearchParams();
      for (const key in payload) {
          formData.append(key, payload[key]);
      }

      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString(),
      });
      const data = await res.json();
      
      if (data.success) {
        toast('儲存成功');
        setShowForm(false);
        setForm(initialForm);
        setEditingId(null);
        loadItems();
      } else {
        toast(data.message || '儲存失敗', 'error');
      }
    } catch (e) {
      toast(e.message, 'error');
    } finally {
      setSubmitting(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    setForm(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? (checked ? 1 : 0) : value
    }));
  };

  return (
    <div>
      <div className="d-flex justify-content-between mb-3">
        <div className="d-flex gap-2">
           <h3>商品列表</h3>
           <select className="form-select w-auto" value={filterCategory} onChange={(e) => setFilterCategory(e.target.value)}>
             <option value="">所有分類</option>
             {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
           </select>
        </div>
        <button className="btn btn-primary" onClick={() => {
          setForm(initialForm);
          setEditingId(null);
          setShowForm(!showForm);
        }}>
          {showForm ? '取消' : '新增商品'}
        </button>
      </div>

      {showForm && (
        <div className="card mb-3">
          <div className="card-body">
            <form onSubmit={handleSubmit}>
              <div className="row g-3">
                <div className="col-md-6">
                  <label className="form-label">名稱 <span className="text-danger">*</span></label>
                  <input type="text" className="form-control" name="name" value={form.name} onChange={handleInputChange} required />
                </div>
                <div className="col-md-6">
                  <label className="form-label">分類 <span className="text-danger">*</span></label>
                  <select className="form-select" name="category_id" value={form.category_id} onChange={handleInputChange} required>
                    <option value="">請選擇</option>
                     {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                  </select>
                </div>
                <div className="col-12">
                  <label className="form-label">描述</label>
                  <textarea className="form-control" name="description" value={form.description} onChange={handleInputChange} rows="2"></textarea>
                </div>
                 <div className="col-md-4">
                  <label className="form-label">原價 <span className="text-danger">*</span></label>
                  <input type="number" step="0.01" className="form-control" name="price_original" value={form.price_original} onChange={handleInputChange} required />
                </div>
                 <div className="col-md-4">
                  <label className="form-label">特價</label>
                  <input type="number" step="0.01" className="form-control" name="price_sale" value={form.price_sale} onChange={handleInputChange} />
                </div>
                 <div className="col-md-4">
                   <label className="form-label">狀態</label>
                   <select className="form-select" name="status" value={form.status} onChange={handleInputChange}>
                     <option value="draft">草稿</option>
                     <option value="published">已發布</option>
                     <option value="archived">已封存</option>
                   </select>
                </div>
                 <div className="col-md-6">
                  <label className="form-label">標籤 (以逗號分隔)</label>
                  <input type="text" className="form-control" name="tags" value={form.tags} onChange={handleInputChange} placeholder="例如: 熱銷,新品" />
                </div>
                 <div className="col-md-6">
                  <label className="form-label">媒體 ID (圖片)</label>
                  <input type="number" className="form-control" name="media_id" value={form.media_id} onChange={handleInputChange} />
                </div>
                <div className="col-md-6 d-flex align-items-end">
                  <label className="form-check form-switch">
                    <input className="form-check-input" type="checkbox" name="is_best_seller" checked={form.is_best_seller === 1} onChange={handleInputChange} />
                    <span className="form-check-label">設為熱銷</span>
                  </label>
                </div>
                <div className="col-12">
                  <button type="submit" className="btn btn-primary" disabled={submitting}>
                    {submitting ? '處理中...' : '儲存'}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      )}

      <div className="card">
        <div className="table-responsive">
          <table className="table table-vcenter card-table">
            <thead>
              <tr>
                <th>名稱</th>
                <th>分類</th>
                <th>價格</th>
                <th>狀態</th>
                <th>標籤</th>
                <th className="w-1">操作</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="6" className="text-center">載入中...</td></tr>
              ) : items.length === 0 ? (
                <tr><td colSpan="6" className="text-center">無資料</td></tr>
              ) : (
                items.map(item => (
                  <tr key={item.id}>
                    <td>
                      {item.is_best_seller === 1 && <span className="badge bg-yellow me-1">熱銷</span>}
                      {item.name}
                    </td>
                    <td>{item.category_name}</td>
                    <td>
                      {item.price_sale ? (
                        <span>
                          <s className="text-muted">{item.price_original}</s> <b className="text-danger">{item.price_sale}</b>
                        </span>
                      ) : (
                        item.price_original
                      )}
                    </td>
                    <td>
                      <span className={`badge bg-${item.status === 'published' ? 'success' : 'secondary'}`}>
                        {item.status}
                      </span>
                    </td>
                     <td>{item.tags}</td>
                    <td>
                      <div className="btn-list flex-nowrap">
                        <button className="btn btn-white btn-sm" onClick={() => handleEdit(item)}>編輯</button>
                        <button className="btn btn-danger btn-sm" onClick={() => handleDelete(item.id)}>刪除</button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

const root = document.getElementById('menu-items-app');
if (root) {
  const apiBase = root.dataset.apiBase;
  const apiCategoryBase = root.dataset.apiCategoryBase;
  ReactDOM.createRoot(root).render(<MenuItemsApp apiBase={apiBase} apiCategoryBase={apiCategoryBase} />);
}
