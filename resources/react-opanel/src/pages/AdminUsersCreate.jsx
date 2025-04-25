import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';

function AdminUsersCreateApp() {
  const [formData, setFormData] = useState({
    username: '',
    password: '',
    confirmPassword: '',
    display_name: '',
    group_id: '',
  });
  
  const [groups, setGroups] = useState([]);
  const [loading, setLoading] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  
  // 載入角色群組列表
  const loadGroups = async () => {
    setLoading(true);
    
    try {
      const response = await fetch('/opanel/access/roles/list');
      const data = await response.json();
      
      setGroups(data.roles || []);
    } catch (error) {
      console.error('載入角色群組列表失敗', error);
      window.Tabler?.Toast?.show('載入角色群組列表失敗', { color: 'red' });
    } finally {
      setLoading(false);
    }
  };
  
  // 初始載入
  useEffect(() => {
    loadGroups();
  }, []);
  
  // 處理表單輸入變更
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value,
    });
  };
  
  // 表單提交
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // 驗證表單
    if (!formData.username) {
      window.Tabler?.Toast?.show('請輸入帳號', { color: 'red' });
      return;
    }
    
    if (!formData.password) {
      window.Tabler?.Toast?.show('請輸入密碼', { color: 'red' });
      return;
    }
    
    if (formData.password !== formData.confirmPassword) {
      window.Tabler?.Toast?.show('兩次輸入的密碼不一致', { color: 'red' });
      return;
    }
    
    setIsSubmitting(true);
    
    try {
      const response = await fetch('/opanel/users/create', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          username: formData.username,
          password: formData.password,
          display_name: formData.display_name,
          group_id: formData.group_id || null,
        }),
      });
      
      const result = await response.json();
      
      if (!result.success) {
        throw new Error(result.message || '新增使用者失敗');
      }
      
      window.Tabler?.Toast?.show('使用者新增成功', { color: 'success' });
      
      // 重定向到使用者列表頁面
      window.location.href = '/opanel/users';
    } catch (error) {
      console.error('新增使用者失敗', error);
      window.Tabler?.Toast?.show(error.message || '新增使用者失敗', { color: 'red' });
    } finally {
      setIsSubmitting(false);
    }
  };
  
  return (
    <div className="container-fluid">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="h3">新增後台使用者</h1>
        <a href="/opanel/users" className="btn btn-secondary">
          <i className="fas fa-arrow-left"></i> 返回列表
        </a>
      </div>

      <div className="card shadow mb-4">
        <div className="card-header py-3">
          <h3 className="m-0 font-weight-bold text-primary">使用者資料</h3>
        </div>
        <div className="card-body">
          {loading ? (
            <div className="text-center py-5">
              <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">載入中...</span>
              </div>
            </div>
          ) : (
            <form onSubmit={handleSubmit}>
              <div className="mb-3">
                <label htmlFor="username" className="form-label">帳號 <span className="text-danger">*</span></label>
                <input 
                  type="text" 
                  className="form-control" 
                  id="username" 
                  name="username"
                  value={formData.username}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  required
                />
                <small className="form-text text-muted">帳號用於登入系統，建立後不可修改</small>
              </div>
              
              <div className="mb-3">
                <label htmlFor="password" className="form-label">密碼 <span className="text-danger">*</span></label>
                <input 
                  type="password" 
                  className="form-control" 
                  id="password" 
                  name="password"
                  value={formData.password}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  required
                />
                <small className="form-text text-muted">請設定安全的密碼</small>
              </div>
              
              <div className="mb-3">
                <label htmlFor="confirmPassword" className="form-label">確認密碼 <span className="text-danger">*</span></label>
                <input 
                  type="password" 
                  className="form-control" 
                  id="confirmPassword" 
                  name="confirmPassword"
                  value={formData.confirmPassword}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  required
                />
                <small className="form-text text-muted">請再次輸入密碼以確認</small>
              </div>
              
              <div className="mb-3">
                <label htmlFor="display_name" className="form-label">顯示名稱</label>
                <input 
                  type="text" 
                  className="form-control" 
                  id="display_name" 
                  name="display_name"
                  value={formData.display_name}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                />
                <small className="form-text text-muted">顯示在系統中的名稱</small>
              </div>
              
              <div className="mb-3">
                <label htmlFor="group_id" className="form-label">角色群組</label>
                <select 
                  className="form-control" 
                  id="group_id" 
                  name="group_id"
                  value={formData.group_id}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                >
                  <option value="">-- 請選擇角色群組 --</option>
                  {groups.map(group => (
                    <option key={group.id} value={group.id}>{group.name}</option>
                  ))}
                </select>
                <small className="form-text text-muted">使用者的權限將依據所屬群組設定</small>
              </div>
              
              <button 
                type="submit" 
                className="btn btn-primary"
                disabled={isSubmitting}
              >
                {isSubmitting ? (
                  <>
                    <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    處理中...
                  </>
                ) : '建立使用者'}
              </button>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('admin-users-create-app')).render(
  <React.StrictMode>
    <AdminUsersCreateApp />
  </React.StrictMode>
);
