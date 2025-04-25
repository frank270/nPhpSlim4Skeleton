import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';

function AdminUsersEditApp() {
  const [userId, setUserId] = useState(null);
  const [userData, setUserData] = useState({
    username: '',
    display_name: '',
    group_id: '',
    status: 1
  });
  
  const [groups, setGroups] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  
  // 重設密碼相關狀態
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [isResettingPassword, setIsResettingPassword] = useState(false);
  
  // 從 URL 獲取使用者 ID
  useEffect(() => {
    const pathParts = window.location.pathname.split('/');
    const id = pathParts[pathParts.indexOf('users') + 1];
    setUserId(id);
  }, []);
  
  // 載入使用者資料
  const loadUserData = async () => {
    if (!userId) return;
    
    setLoading(true);
    
    try {
      const response = await fetch(`/opanel/users/${userId}/edit`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });
      
      const data = await response.json();
      
      if (!data.user) {
        throw new Error('使用者資料不存在');
      }
      
      setUserData(data.user);
    } catch (error) {
      console.error('載入使用者資料失敗', error);
      window.Tabler?.Toast?.show('載入使用者資料失敗', { color: 'red' });
    } finally {
      setLoading(false);
    }
  };
  
  // 載入角色群組列表
  const loadGroups = async () => {
    try {
      const response = await fetch('/opanel/access/roles/list');
      const data = await response.json();
      
      setGroups(data.roles || []);
    } catch (error) {
      console.error('載入角色群組列表失敗', error);
      window.Tabler?.Toast?.show('載入角色群組列表失敗', { color: 'red' });
    }
  };
  
  // 初始載入
  useEffect(() => {
    loadGroups();
  }, []);
  
  // 當 userId 變更時載入使用者資料
  useEffect(() => {
    if (userId) {
      loadUserData();
    }
  }, [userId]);
  
  // 處理表單輸入變更
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setUserData({
      ...userData,
      [name]: value,
    });
  };
  
  // 表單提交
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    setIsSubmitting(true);
    
    try {
      const response = await fetch(`/opanel/users/${userId}/edit`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          display_name: userData.display_name,
          group_id: userData.group_id || null,
          status: userData.status,
        }),
      });
      
      const result = await response.json();
      
      if (!result.success) {
        throw new Error(result.message || '更新使用者資料失敗');
      }
      
      window.Tabler?.Toast?.show('使用者資料更新成功', { color: 'success' });
    } catch (error) {
      console.error('更新使用者資料失敗', error);
      window.Tabler?.Toast?.show(error.message || '更新使用者資料失敗', { color: 'red' });
    } finally {
      setIsSubmitting(false);
    }
  };
  
  // 重設密碼
  const resetPassword = async (e) => {
    e.preventDefault();
    
    if (newPassword !== confirmPassword) {
      window.Tabler?.Toast?.show('兩次輸入的密碼不一致', { color: 'red' });
      return;
    }
    
    setIsResettingPassword(true);
    
    try {
      const response = await fetch(`/opanel/users/${userId}/reset-password`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ password: newPassword }),
      });
      
      const result = await response.json();
      
      if (!result.success) {
        throw new Error(result.message || '重設密碼失敗');
      }
      
      window.Tabler?.Toast?.show('密碼重設成功', { color: 'success' });
      setNewPassword('');
      setConfirmPassword('');
    } catch (error) {
      console.error('重設密碼失敗', error);
      window.Tabler?.Toast?.show(error.message || '重設密碼失敗', { color: 'red' });
    } finally {
      setIsResettingPassword(false);
    }
  };
  
  return (
    <div className="container-fluid">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="h3">編輯後台使用者</h1>
        <a href="/opanel/users" className="btn btn-secondary">
          <i className="fas fa-arrow-left"></i> 返回列表
        </a>
      </div>

      {loading ? (
        <div className="text-center py-5">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">載入中...</span>
          </div>
        </div>
      ) : (
        <>
          <div className="card shadow mb-4">
            <div className="card-header py-3">
              <h3 className="m-0 font-weight-bold text-primary">使用者資料</h3>
            </div>
            <div className="card-body">
              <form onSubmit={handleSubmit}>
                <div className="mb-3">
                  <label htmlFor="username" className="form-label">帳號</label>
                  <input 
                    type="text" 
                    className="form-control" 
                    id="username" 
                    value={userData.username}
                    disabled
                    readOnly
                  />
                  <small className="form-text text-muted">帳號不可修改</small>
                </div>
                
                <div className="mb-3">
                  <label htmlFor="display_name" className="form-label">顯示名稱</label>
                  <input 
                    type="text" 
                    className="form-control" 
                    id="display_name" 
                    name="display_name"
                    value={userData.display_name || ''}
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
                    value={userData.group_id || ''}
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
                
                <div className="mb-3">
                  <label htmlFor="status" className="form-label">狀態</label>
                  <select 
                    className="form-control" 
                    id="status" 
                    name="status"
                    value={userData.status}
                    onChange={handleInputChange}
                    disabled={isSubmitting}
                  >
                    <option value="1">啟用</option>
                    <option value="0">停用</option>
                  </select>
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
                  ) : '更新使用者'}
                </button>
              </form>
            </div>
          </div>
          
          <div className="card shadow mb-4">
            <div className="card-header py-3">
              <h6 className="m-0 font-weight-bold text-danger">重設密碼</h6>
            </div>
            <div className="card-body">
              <form onSubmit={resetPassword}>
                <div className="mb-3">
                  <label htmlFor="new_password" className="form-label">新密碼</label>
                  <input 
                    type="password" 
                    className="form-control" 
                    id="new_password"
                    value={newPassword}
                    onChange={(e) => setNewPassword(e.target.value)}
                    disabled={isResettingPassword}
                    required
                  />
                </div>
                
                <div className="mb-3">
                  <label htmlFor="confirm_password" className="form-label">確認密碼</label>
                  <input 
                    type="password" 
                    className="form-control" 
                    id="confirm_password"
                    value={confirmPassword}
                    onChange={(e) => setConfirmPassword(e.target.value)}
                    disabled={isResettingPassword}
                    required
                  />
                </div>
                
                <button 
                  type="submit" 
                  className="btn btn-warning"
                  disabled={isResettingPassword}
                >
                  {isResettingPassword ? (
                    <>
                      <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                      處理中...
                    </>
                  ) : '重設密碼'}
                </button>
              </form>
            </div>
          </div>
        </>
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('admin-users-edit-app')).render(
  <React.StrictMode>
    <AdminUsersEditApp />
  </React.StrictMode>
);
