import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import { mockRoles, mockPermissions, mockMatrix } from '../data/mockAccessRoles';

function AccessRolesApp() {
  const [groupId, setGroupId] = useState(null);
  const [matrix, setMatrix] = useState(() => ({ ...mockMatrix }));
  const [loading, setLoading] = useState(false);
  const [roles, setRoles] = useState([]);
  const [rolesLoading, setRolesLoading] = useState(true);
  const [permissions, setPermissions] = useState([]);
  const [showModal, setShowModal] = useState(false);
  const [newRole, setNewRole] = useState({ code: '', name: '', memo: '' });
  const [formError, setFormError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [roleToDelete, setRoleToDelete] = useState(null);
  const [isDeleting, setIsDeleting] = useState(false);

  const handleRoleSelect = (roleId) => {
    setGroupId(roleId);
    setLoading(true);
  
    // Step 1: 抓取權限清單（含勾選狀態）
    fetch(`/opanel/access/group/${roleId}/permissions`)
      .then((res) => res.json())
      .then((data) => {
        setPermissions(data.permissions || []);
      })
      .catch(() => {
        window.Tabler.Toast.show('載入權限失敗', { color: 'red' });
        setPermissions([]); // 顯示為空表
      })
      .finally(() => {
        setLoading(false);
      });
  };
  
  const handleDeleteRole = async () => {
    if (!roleToDelete) return;
    
    setIsDeleting(true);
    
    try {
      const response = await fetch(`/opanel/access/roles/${roleToDelete.id}`, {
        method: 'DELETE',
      });
      
      const result = await response.json();
      
      if (!result.success) {
        throw new Error(result.message || '刪除失敗');
      }
      
      // 更新角色列表
      setRoles(roles.filter(role => role.id !== roleToDelete.id));
      window.showToast('角色已成功刪除', 'success');
      
      // 如果刪除的是當前選中的角色，清空選擇
      if (groupId === roleToDelete.id) {
        setGroupId(null);
        setPermissions([]);
      }
      
      // 關閉確認對話框
      setRoleToDelete(null);
    } catch (error) {
      window.showToast(`刪除失敗: ${error.message}`, 'error');
    } finally {
      setIsDeleting(false);
    }
  };
  
  const handleCreateRole = async (e) => {
    e.preventDefault();
    setFormError('');
    setIsSubmitting(true);
    
    try {
      const formData = new URLSearchParams();
      formData.append('code', newRole.code);
      formData.append('name', newRole.name);
      formData.append('memo', newRole.memo);
      
      const response = await fetch('/opanel/access/roles/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
      });
      
      const result = await response.json();
      
      if (!result.success) {
        throw new Error(result.message || '新增失敗');
      }
      
      // 更新角色列表
      setRoles([...roles, result.role]);
      window.showToast('角色新增成功', 'success');
      
      // 重置表單並關閉彈窗
      setNewRole({ code: '', name: '', memo: '' });
      setShowModal(false);
    } catch (error) {
      setFormError(error.message);
    } finally {
      setIsSubmitting(false);
    }
  };
  
  
  useEffect(() => {
    fetch('/opanel/access/roles/list')
      .then((res) => res.json())
      .then((data) => {
        setRoles(data.roles || []);
      })
      .catch(() => {
        window.showToast('角色清單載入失敗', 'error');
      })
      .finally(() => {
        setRolesLoading(false);
      });
  }, []);
  
  
  const handlePermissionChange = async ({ groupId, funcId, enabled }) => {
    try {
        // 確保 groupId 和 funcId 是數字
        const formData = new URLSearchParams();
        formData.append('groupId', Number(groupId));
        formData.append('funcId', Number(funcId));
        formData.append('enabled', enabled ? '1' : '0');
        
        console.log('發送資料:', {
            groupId: Number(groupId),
            funcId: Number(funcId),
            enabled: enabled ? '1' : '0'
        }); // 除錯訊息
        
        const response = await fetch('/opanel/access/update-permission', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });

        const result = await response.json(); // 獲取回應內容
        console.log('伺服器回應:', result); // 除錯訊息
        
        if (!result.success) {
            throw new Error(result.message || '伺服器錯誤');
        }

        window.showToast('權限已更新', 'success');
    } catch (error) {
        window.showToast(`更新失敗: ${error.message}`, 'error');
        
        // 還原前端狀態（取消樂觀 UI 更新）
        if (groupId) {
            // 重新載入權限資料
            fetch(`/opanel/access/group/${groupId}/permissions`)
                .then((res) => res.json())
                .then((data) => {
                    setPermissions(data.permissions || []);
                })
                .catch(() => {
                    window.showToast('重新載入權限失敗', 'error');
                });
        }
    }
  };
  return (
    <div className="container-xl mt-4">
      <div className="row">
        {/* 左側角色清單 */}
        <div className="col-md-3">
          <div className="card">
            <div className="card-header d-flex justify-content-between align-items-center">
              <h3 className="card-title">角色清單</h3>
              <button 
                className="btn btn-primary btn-sm" 
                onClick={() => setShowModal(true)}
              >
                新增角色
              </button>
            </div>
            <div className="card-body">
            {rolesLoading ? (
                <div className="text-muted">角色清單載入中...</div>
              ) : roles.length === 0 ? (
                <div className="text-danger">找不到角色資料</div>
              ) : (
                roles.map((role) => (
                  <div key={role.id} className="d-flex mb-2 align-items-center">
                  <button
                    onClick={() => handleRoleSelect(role.id)}
                    className={`btn flex-grow-1 me-1 ${
                      role.id === groupId ? 'btn-primary' : 'btn-outline-primary'
                    }`}
                  >
                    {role.name}
                  </button>
                  <button 
                    className="btn btn-outline-danger btn-icon" 
                    title="刪除角色"
                    onClick={(e) => {
                      e.stopPropagation();
                      setRoleToDelete(role);
                    }}
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
                ))
              )}
            </div>
          </div>
        </div>

        {/* 右側預留：權限清單 */}
        <div className="col-md-9">
          <div className="card">
          <div className="card-header">
            <h3 className="card-title">權限清單</h3>
            </div>
            <div className="card-body">
            {groupId === null ? (
              <div className="text-muted">請先選擇一個角色</div>
            ) : loading ? (
              <div className="text-muted">載入中...</div>
            ) : (
              <table className="table">
                <thead>
                  <tr>
                    <th>功能名稱</th>
                    <th className="text-end">是否啟用</th>
                  </tr>
                </thead>
                <tbody>
                {permissions.map((perm) => (
                <tr key={perm.id}>
                    <td>{perm.name}</td>
                    <td className="text-end">
                    <input
                        type="checkbox"
                        className="form-check-input"
                        checked={perm.enabled === 1}
                        disabled={loading}
                        onChange={(e) => {
                        const checked = e.target.checked;

                        // 更新後端
                        handlePermissionChange({
                            groupId,
                            funcId: perm.id,
                            enabled: checked,
                        });

                        // 更新前端狀態（樂觀 UI）
                        setPermissions((prev) =>
                            prev.map((p) =>
                            p.id === perm.id ? { ...p, enabled: checked ? 1 : 0 } : p
                            )
                        );
                        }}
                    />
                    </td>
                </tr>
                ))}
                </tbody>
              </table>
            )}
            </div>
          </div>
        </div>
      </div>
      
      {showModal && (
        <div className="modal d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">新增角色</h5>
                <button 
                  type="button" 
                  className="btn-close" 
                  onClick={() => {
                    setShowModal(false);
                    setFormError('');
                    setNewRole({ code: '', name: '', memo: '' });
                  }}
                ></button>
              </div>
              <div className="modal-body">
                {formError && <div className="alert alert-danger">{formError}</div>}
                <form onSubmit={handleCreateRole}>
                  <div className="mb-3">
                    <label className="form-label">角色代碼</label>
                    <input 
                      type="text" 
                      className="form-control" 
                      value={newRole.code}
                      onChange={(e) => setNewRole({...newRole, code: e.target.value})}
                      required
                    />
                    <div className="form-text">英文字母和數字，不含空格，例如：editor</div>
                  </div>
                  <div className="mb-3">
                    <label className="form-label">角色名稱</label>
                    <input 
                      type="text" 
                      className="form-control" 
                      value={newRole.name}
                      onChange={(e) => setNewRole({...newRole, name: e.target.value})}
                      required
                    />
                    <div className="form-text">顯示用名稱，例如：編輯人員</div>
                  </div>
                  <div className="mb-3">
                    <label className="form-label">備註說明</label>
                    <textarea 
                      className="form-control" 
                      value={newRole.memo}
                      onChange={(e) => setNewRole({...newRole, memo: e.target.value})}
                    ></textarea>
                  </div>
                  <div className="modal-footer">
                    <button 
                      type="button" 
                      className="btn btn-secondary" 
                      onClick={() => {
                        setShowModal(false);
                        setFormError('');
                        setNewRole({ code: '', name: '', memo: '' });
                      }}
                    >
                      取消
                    </button>
                    <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
                      {isSubmitting ? (
                        <>
                          <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                          處理中...
                        </>
                      ) : '新增'}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      )}
      
      {/* 刪除角色確認對話框 */}
      {roleToDelete && (
        <div className="modal d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog modal-sm">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">確認刪除</h5>
                <button 
                  type="button" 
                  className="btn-close" 
                  onClick={() => setRoleToDelete(null)}
                  disabled={isDeleting}
                ></button>
              </div>
              <div className="modal-body">
                <p>您確定要刪除角色「{roleToDelete.name}」嗎？</p>
                <p className="text-danger">此操作無法復原，角色的所有權限設定將一併刪除。</p>
              </div>
              <div className="modal-footer">
                <button 
                  type="button" 
                  className="btn btn-secondary" 
                  onClick={() => setRoleToDelete(null)}
                  disabled={isDeleting}
                >
                  取消
                </button>
                <button 
                  type="button" 
                  className="btn btn-danger" 
                  onClick={handleDeleteRole}
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

ReactDOM.createRoot(document.getElementById('roles-app')).render(
  <React.StrictMode>
    <AccessRolesApp />
  </React.StrictMode>
);
