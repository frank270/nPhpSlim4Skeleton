import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom/client';
import { useTranslation } from 'react-i18next';
import '../i18n';

const initialForm = {
  name: '',
  description: '',
  category_id: '',
  report_date: '',
  expired_at: '',
  image_path: '',
  file_path: '',
  tags: '',
  is_highlight: 0,
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

const STATUS_MAP = {
  draft: '草稿',
  published: '已發布',
  archived: '已封存'
};

// Custom Tags Input Component (Reused)
const TagsInput = ({ value, onChange, placeholder }) => {
  const [inputValue, setInputValue] = useState('');

  const handleKeyDown = (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const val = inputValue.trim();
      if (val && !value.includes(val)) {
        onChange([...value, val]);
        setInputValue('');
      }
    } else if (e.key === 'Backspace' && !inputValue && value.length > 0) {
      onChange(value.slice(0, -1));
    }
  };

  const removeTag = (index) => {
    onChange(value.filter((_, i) => i !== index));
  };

  return (
    <div className="form-control d-flex flex-wrap gap-2 align-items-center" onClick={() => document.getElementById('tags-input-field')?.focus()}>
      {value.map((tag, index) => (
        <span key={index} className="badge bg-secondary text-white d-flex align-items-center gap-1">
          {tag}
          <span 
            className="cursor-pointer text-white opacity-75 hover-opacity-100" 
            style={{ cursor: 'pointer' }}
            onClick={(e) => { e.stopPropagation(); removeTag(index); }}
          >×</span>
        </span>
      ))}
      <input
        id="tags-input-field"
        type="text"
        className="border-0 p-0"
        style={{ outline: 'none', minWidth: '100px', flex: 1 }}
        value={inputValue}
        onChange={(e) => setInputValue(e.target.value)}
        onKeyDown={handleKeyDown}
        placeholder={value.length === 0 ? placeholder : ''}
      />
    </div>
  );
};

function FoodSafetyItemsApp({ apiBase, apiCategoryBase }) {

  const { t } = useTranslation();
  const [items, setItems] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState(initialForm);
  const [editingId, setEditingId] = useState(null);
  const [submitting, setSubmitting] = useState(false);
  const [uploadProgress, setUploadProgress] = useState(null);
  const [fileUploadProgress, setFileUploadProgress] = useState(null);
  const [uploading, setUploading] = useState(false);
  
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
      report_date: item.report_date || '',
      expired_at: item.expired_at || '',
      image_path: item.image_path || '',
      file_path: item.file_path || '',
      tags: item.tags || '',
      is_highlight: item.is_highlight,
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
      if (!payload.image_path) payload.image_path = '';
      if (!payload.file_path) payload.file_path = '';
      if (!payload.report_date) payload.report_date = '';
      if (!payload.expired_at) payload.expired_at = '';

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

  const handleUpload = (file, type) => {
      if (!file) return;
      if (type === 'image') setUploadProgress(0);
      else setFileUploadProgress(0);
      setUploading(true);

      const formData = new FormData();
      formData.append('file', file);
      formData.append('uploaded_from', 'food-safety');

      const xhr = new XMLHttpRequest();
      xhr.open('POST', '/opanel/media-assets/upload');
      
      xhr.upload.onprogress = (event) => {
        if (event.lengthComputable) {
          const percent = Math.round((event.loaded / event.total) * 100);
          if (type === 'image') setUploadProgress(percent);
          else setFileUploadProgress(percent);
        }
      };

      xhr.onload = () => {
        if (type === 'image') setUploadProgress(100);
        else setFileUploadProgress(100);

        setTimeout(() => {
            try {
              const data = JSON.parse(xhr.responseText);
              if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                 if (type === 'image') {
                     setForm(prev => ({ ...prev, image_path: data.data.path }));
                     toast('圖片上傳成功');
                 } else {
                     setForm(prev => ({ ...prev, file_path: data.data.path }));
                     toast('檔案上傳成功');
                 }
              } else {
                 toast(data.message || '上傳失敗', 'error');
              }
            } catch (err) {
              toast('上傳回應解析錯誤', 'error');
            } finally {
               if (type === 'image') setUploadProgress(null);
               else setFileUploadProgress(null);
               setUploading(false);
               
               const inputId = type === 'image' ? 'item-image-upload' : 'item-file-upload';
               const input = document.getElementById(inputId);
               if (input) input.value = '';
            }
        }, 500); 
      };

      xhr.onerror = () => {
        toast('上傳發生網路錯誤', 'error');
        if (type === 'image') setUploadProgress(null);
        else setFileUploadProgress(null);
        setUploading(false);
      };

      xhr.send(formData);
  };

  return (
    <div>
      <div className="d-flex justify-content-between mb-3">
        <div className="d-flex gap-2">
           <h3>檢驗報告列表</h3>
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
          {showForm ? '取消' : '新增報告'}
        </button>
      </div>

      {showForm && (
        <div className="card mb-3">
          <div className="card-body">
            <form onSubmit={handleSubmit}>
              <div className="row g-3">
                <div className="col-md-6">
                  <label className="form-label">報告名稱 <span className="text-danger">*</span></label>
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
                  <label className="form-label">檢驗日期</label>
                  <input type="date" className="form-control" name="report_date" value={form.report_date} onChange={handleInputChange} />
                </div>
                 <div className="col-md-4">
                  <label className="form-label">到期日期</label>
                  <input type="date" className="form-control" name="expired_at" value={form.expired_at} onChange={handleInputChange} />
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
                   <label className="form-label">標籤</label>
                   <TagsInput 
                      value={form.tags ? form.tags.split(',').filter(t => t) : []} 
                      onChange={(tags) => setForm(prev => ({ ...prev, tags: tags.join(',') }))}
                      placeholder='輸入後按 Enter'
                   />
                </div>
                
                {/* Image Upload */}
                <div className="col-md-6">
                   <label className="form-label">預覽圖片 (列表顯示)</label>
                   <div className="d-flex align-items-start gap-3">
                      {form.image_path && (
                        <div className="position-relative">
                          <img 
                            src={form.image_path.startsWith('http') ? form.image_path : `${window.location.origin}/${form.image_path}`} 
                            alt="Preview" 
                            className="img-thumbnail"
                            style={{ width: '100px', height: '100px', objectFit: 'cover' }}
                          />
                          <button 
                            type="button" 
                            className="btn-close position-absolute top-0 end-0 bg-white" 
                            onClick={() => setForm(prev => ({ ...prev, image_path: '' }))}
                          ></button>
                        </div>
                      )}
                      <div className="flex-fill">
                         <input 
                            type="file" 
                            id="item-image-upload"
                            className="form-control" 
                            accept="image/*"
                            disabled={uploading}
                            onChange={(e) => handleUpload(e.target.files[0], 'image')}
                         />
                         {uploadProgress !== null && (
                            <div className="progress mt-2" style={{ height: '3px' }}>
                              <div className="progress-bar" style={{ width: `${uploadProgress}%` }}></div>
                            </div>
                         )}
                      </div>
                   </div>
                </div>

                {/* File Upload */}
                <div className="col-12">
                   <label className="form-label">檢驗報告檔案 (PDF)</label>
                   <div className="d-flex align-items-center gap-3">
                      {form.file_path && (
                        <div className="border rounded p-2 d-flex align-items-center gap-2">
                           <i className="ti ti-file-text"></i>
                           <a href={form.file_path.startsWith('http') ? form.file_path : `${window.location.origin}/${form.file_path}`} target="_blank" rel="noopener noreferrer">
                              {form.file_path.split('/').pop()}
                           </a>
                           <button type="button" className="btn-close ms-2" onClick={() => setForm(prev => ({ ...prev, file_path: '' }))}></button>
                        </div>
                      )}
                      
                       <div className="flex-fill">
                         <input 
                            type="file" 
                            id="item-file-upload"
                            className="form-control" 
                            accept=".pdf,application/pdf"
                            disabled={uploading}
                            onChange={(e) => handleUpload(e.target.files[0], 'file')}
                         />
                         {fileUploadProgress !== null && (
                            <div className="progress mt-2" style={{ height: '3px' }}>
                              <div className="progress-bar" style={{ width: `${fileUploadProgress}%` }}></div>
                            </div>
                         )}
                      </div>
                   </div>
                </div>

                <div className="col-md-6 d-flex align-items-end">
                  <label className="form-check form-switch">
                    <input className="form-check-input" type="checkbox" name="is_highlight" checked={form.is_highlight === 1} onChange={handleInputChange} />
                    <span className="form-check-label">置頂/重點標記</span>
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
                <th>檢驗日期</th>
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
                      {item.is_highlight === 1 && <span className="badge bg-yellow me-1">置頂</span>}
                      {item.name}
                      {item.file_path && <i className="ti ti-paperclip ms-1 text-muted"></i>}
                    </td>
                    <td>{item.category_name}</td>
                    <td>{item.report_date || '-'}</td>
                    <td>
                      <span className={`badge text-white bg-${item.status === 'published' ? 'success' : 'secondary'}`}>
                        {STATUS_MAP[item.status] || item.status}
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

const root = document.getElementById('food-safety-items-root');
if (root) {
  const apiBase = window.PAGE_CONFIG.apiBase;
  const apiCategoryBase = window.PAGE_CONFIG.apiCategoryBase;
  ReactDOM.createRoot(root).render(<FoodSafetyItemsApp apiBase={apiBase} apiCategoryBase={apiCategoryBase} />);
}
