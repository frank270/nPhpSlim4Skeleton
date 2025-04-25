import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';

function AdminUsersApp() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [groups, setGroups] = useState([]);
  const [groupFilter, setGroupFilter] = useState('');
  const [keywordFilter, setKeywordFilter] = useState('');
  
  // 重設密碼相關狀態
  const [showResetPasswordModal, setShowResetPasswordModal] = useState(false);
  const [resetPasswordUser, setResetPasswordUser] = useState(null);
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [isResettingPassword, setIsResettingPassword] = useState(false);
  
  // 刪除使用者相關狀態
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [userToDelete, setUserToDelete] = useState(null);
  const [isDeleting, setIsDeleting] = useState(false);
  
  // 載入使用者列表
  const loadUsers = async () => {
    setLoading(true);
    
    try {
      let url = '/opanel/users/list';
      const params = new URLSearchParams();
      
      if (groupFilter) {
        params.append('group_id', groupFilter);
      }
      
      if (keywordFilter) {
        params.append('keyword', keywordFilter);
      }
      
      if (params.toString()) {
        url += '?' + params.toString();
      }
      
      const response = await fetch(url);
      const data = await response.json();
      
      setUsers(data.users || []);
    } catch (error) {
      console.error('載入使用者列表失敗', error);
      window.showToast('載入使用者列表失敗', 'error');
      setUsers([]);
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
      window.showToast('載入角色群組列表失敗', 'error');
      setGroups([]);
    }
  };
  
  // 切換使用者狀態
  const toggleUserStatus = async (userId, currentStatus) => {
    try {
      const response = await fetch(`/opanel/users/${userId}/toggle-status`, {
        method: 'POST',
      });
      
      const result = await response.json();
      
      if (!result.success) {
        throw new Error(result.message || '操作失敗');
      }
      
      // 更新使用者列表中的狀態
      setUsers(users.map(user => {
        if (user.id === userId) {
          return { ...user, status: currentStatus === 1 ? 0 : 1 };
        }
        return user;
      }));
      
      window.showToast(result.message || '狀態已更新', 'success');
    } catch (error) {
      console.error('切換使用者狀態失敗', error);
      window.showToast(error.message || '切換使用者狀態失敗', 'error');
    }
  };
  
  // 重設使用者密碼
  const resetPassword = async () => {
    if (!resetPasswordUser) return;
    
    if (newPassword !== confirmPassword) {
      window.showToast('兩次輸入的密碼不一致', 'error');
      return;
    }
    
    setIsResettingPassword(true);
    
    try {
      const response = await fetch(`/opanel/users/${resetPasswordUser.id}/reset-password`, {
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
      
      window.showToast('密碼重設成功', 'success');
      setShowResetPasswordModal(false);
      setNewPassword('');
      setConfirmPassword('');
    } catch (error) {
      console.error('重設密碼失敗', error);
      window.showToast(error.message || '重設密碼失敗', 'error');
    } finally {
      setIsResettingPassword(false);
    }
  };
  
  // 刪除使用者
  const deleteUser = async () => {
    if (!userToDelete) return;
    
    setIsDeleting(true);
    
    try {
      const response = await fetch(`/opanel/users/${userToDelete.id}/delete`, {
        method: 'POST',
      });
      
      const result = await response.json();
      
      if (!result.success) {
        throw new Error(result.message || '刪除使用者失敗');
      }
      
      // 更新使用者列表
      setUsers(users.filter(user => user.id !== userToDelete.id));
      window.showToast('使用者已刪除', 'success');
      setShowDeleteModal(false);
    } catch (error) {
      console.error('刪除使用者失敗', error);
      window.showToast(error.message || '刪除使用者失敗', 'error');
    } finally {
      setIsDeleting(false);
    }
  };
  
  // 初始載入
  useEffect(() => {
    loadGroups();
    loadUsers();
  }, []);
  
  // 搜尋按鈕點擊事件
  const handleSearch = () => {
    loadUsers();
  };
  
  return (
    <div className="container-fluid">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="h2 mb-0">後台使用者管理</h1>
        <a href="/opanel/users/create" className="btn btn-primary">
          <i className="ti ti-plus"></i> 新增使用者
        </a>
      </div>

      <div className="card shadow mb-4">
        <div className="card-header py-3 d-flex justify-content-between align-items-center">
          <h3 className="m-0 font-weight-bold text-primary">使用者列表</h3>
          <div className="d-flex">
            <select 
              className="form-control mr-2"
              value={groupFilter}
              onChange={(e) => setGroupFilter(e.target.value)}
            >
              <option value="">所有角色群組</option>
              {groups.map(group => (
                <option key={group.id} value={group.id}>{group.name}</option>
              ))}
            </select>
            <input 
              type="text" 
              className="form-control mr-2" 
              placeholder="搜尋使用者..." 
              value={keywordFilter}
              onChange={(e) => setKeywordFilter(e.target.value)}
            />
            <button 
              className="btn btn-outline-primary"
              onClick={handleSearch}
            >
              <i className="ti ti-search me-1"></i> 搜尋
            </button>
          </div>
        </div>
        <div className="card-body">
          <div className="table-responsive">
            <table className="table table-bordered" width="100%" cellSpacing="0">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>帳號</th>
                  <th>顯示名稱</th>
                  <th>角色群組</th>
                  <th>狀態</th>
                  <th>最後登入時間</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr>
                    <td colSpan="7" className="text-center">
                      <div className="spinner-border text-primary" role="status">
                        <span className="visually-hidden">載入中...</span>
                      </div>
                    </td>
                  </tr>
                ) : users.length === 0 ? (
                  <tr>
                    <td colSpan="7" className="text-center">沒有符合條件的使用者</td>
                  </tr>
                ) : (
                  users.map(user => (
                    <tr key={user.id}>
                      <td>{user.id}</td>
                      <td>{user.username}</td>
                      <td>{user.display_name || '-'}</td>
                      <td>{user.group_name || '-'}</td>
                      <td>
                        {user.status === 1 ? (
                          <span className="badge bg-success text-white">啟用</span>
                        ) : (
                          <span className="badge bg-danger text-white">停用</span>
                        )}
                      </td>
                      <td>{user.last_login_at || '-'}</td>
                      <td>
                        <div className="d-flex gap-1">
                          <a href={`/opanel/users/${user.id}/edit`} className="btn btn-sm btn-primary" title="編輯">
                            <svg xmlns="http://www.w3.org/2000/svg" className="icon" width="24" height="24" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor" fill="none" strokeLinecap="round" strokeLinejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                              <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                              <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                              <path d="M16 5l3 3" />
                            </svg>
                          </a>
                          <button 
                            className="btn btn-sm btn-warning"
                            onClick={() => {
                              setResetPasswordUser(user);
                              setShowResetPasswordModal(true);
                            }}
                            title="重設密碼"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" className="icon" width="24" height="24" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor" fill="none" strokeLinecap="round" strokeLinejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                              <path d="M14 6l7 7l-4 4" />
                              <path d="M5.5 13.5l4.5 4.5" />
                              <path d="M5 3a2 2 0 0 0 -2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2 -2v-6" />
                              <path d="M12 11v-4a3 3 0 1 1 6 0v4" />
                            </svg>
                          </button>
                          <button 
                            className={`btn btn-sm ${user.status === 1 ? 'btn-secondary' : 'btn-success'}`}
                            onClick={() => toggleUserStatus(user.id, user.status)}
                            title={user.status === 1 ? '停用' : '啟用'}
                          >
                            {user.status === 1 ? (
                              <svg xmlns="http://www.w3.org/2000/svg" className="icon" width="24" height="24" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor" fill="none" strokeLinecap="round" strokeLinejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                <path d="M5.7 5.7l12.6 12.6" />
                              </svg>
                            ) : (
                              <svg xmlns="http://www.w3.org/2000/svg" className="icon" width="24" height="24" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor" fill="none" strokeLinecap="round" strokeLinejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M5 12l5 5l10 -10" />
                              </svg>
                            )}
                          </button>
                          <button 
                            className="btn btn-sm btn-danger"
                            onClick={() => {
                              setUserToDelete(user);
                              setShowDeleteModal(true);
                            }}
                            title="刪除"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" className="icon" width="24" height="24" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor" fill="none" strokeLinecap="round" strokeLinejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                              <path d="M4 7l16 0" />
                              <path d="M10 11l0 6" />
                              <path d="M14 11l0 6" />
                              <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                              <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                            </svg>
                          </button>
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
      
      {/* 重設密碼 Modal */}
      {showResetPasswordModal && (
        <div className="modal d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">
                  重設 {resetPasswordUser?.username} 的密碼
                </h5>
                <button 
                  type="button" 
                  className="btn-close" 
                  onClick={() => {
                    setShowResetPasswordModal(false);
                    setNewPassword('');
                    setConfirmPassword('');
                  }}
                  disabled={isResettingPassword}
                ></button>
              </div>
              <div className="modal-body">
                <div className="mb-3">
                  <label htmlFor="new-password" className="form-label">新密碼</label>
                  <input 
                    type="password" 
                    className="form-control" 
                    id="new-password"
                    value={newPassword}
                    onChange={(e) => setNewPassword(e.target.value)}
                    disabled={isResettingPassword}
                  />
                </div>
                <div className="mb-3">
                  <label htmlFor="confirm-password" className="form-label">確認密碼</label>
                  <input 
                    type="password" 
                    className="form-control" 
                    id="confirm-password"
                    value={confirmPassword}
                    onChange={(e) => setConfirmPassword(e.target.value)}
                    disabled={isResettingPassword}
                  />
                </div>
              </div>
              <div className="modal-footer">
                <button 
                  type="button" 
                  className="btn btn-secondary" 
                  onClick={() => {
                    setShowResetPasswordModal(false);
                    setNewPassword('');
                    setConfirmPassword('');
                  }}
                  disabled={isResettingPassword}
                >
                  取消
                </button>
                <button 
                  type="button" 
                  className="btn btn-primary" 
                  onClick={resetPassword}
                  disabled={isResettingPassword}
                >
                  {isResettingPassword ? (
                    <>
                      <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                      處理中...
                    </>
                  ) : '確認重設'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
      
      {/* 刪除確認 Modal */}
      {showDeleteModal && (
        <div className="modal d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog modal-sm">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">確認刪除</h5>
                <button 
                  type="button" 
                  className="btn-close" 
                  onClick={() => setShowDeleteModal(false)}
                  disabled={isDeleting}
                ></button>
              </div>
              <div className="modal-body">
                <p>確定要刪除使用者 <strong>{userToDelete?.username}</strong> 嗎？</p>
                <p className="text-danger">此操作無法復原。</p>
              </div>
              <div className="modal-footer">
                <button 
                  type="button" 
                  className="btn btn-secondary" 
                  onClick={() => setShowDeleteModal(false)}
                  disabled={isDeleting}
                >
                  取消
                </button>
                <button 
                  type="button" 
                  className="btn btn-danger" 
                  onClick={deleteUser}
                  disabled={isDeleting}
                >
                  {isDeleting ? (
                    <>
                      <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                      處理中...
                    </>
                  ) : '確認刪除'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('admin-users-app')).render(
  <React.StrictMode>
    <AdminUsersApp />
  </React.StrictMode>
);
