import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import { useTranslation } from 'react-i18next';
import '../i18n';
import { mockRoles, mockPermissions, mockMatrix } from '../data/mockAccessRoles';

function AccessRolesApp() {
  const { t } = useTranslation();
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
        window.Tabler.Toast.show(t('common.error'), { color: 'red' });
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
        throw new Error(result.message || t('access_roles.delete_failed'));
      }
      
      // 更新角色列表
      setRoles(roles.filter(role => role.id !== roleToDelete.id));
      window.showToast(t('access_roles.delete_success'), 'success');
      
      // 如果刪除的是當前選中的角色，清空選擇
      if (groupId === roleToDelete.id) {
        setGroupId(null);
        setPermissions([]);
      }
      
      // 關閉確認對話框
      setRoleToDelete(null);
    } catch (error) {
      window.showToast(`${t('access_roles.delete_failed')}: ${error.message}`, 'error');
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
        throw new Error(result.message || t('common.error'));
      }
      
      // 更新角色列表
      setRoles([...roles, result.role]);
      window.showToast(t('access_roles.create_success'), 'success');
      
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
        window.showToast(t('common.error'), 'error');
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
            throw new Error(result.message || t('common.error'));
        }

        window.showToast(t('access_roles.permission_updated'), 'success');
    } catch (error) {
        window.showToast(`${t('common.error')}: ${error.message}`, 'error');
        
        // 還原前端狀態（取消樂觀 UI 更新）
        if (groupId) {
            // 重新載入權限資料
            fetch(`/opanel/access/group/${groupId}/permissions`)
                .then((res) => res.json())
                .then((data) => {
                    setPermissions(data.permissions || []);
                })
                .catch(() => {
                    window.showToast(t('common.error'), 'error');
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
              <h3 className="card-title">{t('access_roles.title')}</h3>
              <button 
                className="btn btn-primary btn-sm" 
                onClick={() => setShowModal(true)}
              >
                {t('access_roles.add_new')}
              </button>
            </div>
            <div className="card-body">
            {rolesLoading ? (
                <div className="text-muted">{t('common.loading')}</div>
              ) : roles.length === 0 ? (
                <div className="text-danger">{t('access_roles.no_roles')}</div>
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
                    title={t('common.delete')}
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
            <h3 className="card-title">{t('access_roles.permissions_list')}</h3>
            </div>
            <div className="card-body">
            {groupId === null ? (
              <div className="text-muted">{t('access_roles.select_role_hint')}</div>
            ) : loading ? (
              <div className="text-muted">{t('common.loading')}</div>
            ) : (
              <table className="table">
                <thead>
                  <tr>
                    <th>{t('access_roles.func_name')}</th>
                    <th className="text-end">{t('access_roles.enabled')}</th>
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
                <h5 className="modal-title">{t('access_roles.add_new')}</h5>
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
                    <label className="form-label">{t('access_roles.code')}</label>
                    <input 
                      type="text" 
                      className="form-control" 
                      value={newRole.code}
                      onChange={(e) => setNewRole({...newRole, code: e.target.value})}
                      required
                    />
                    <div className="form-text">{t('access_roles.placeholder.code')}</div>
                  </div>
                  <div className="mb-3">
                    <label className="form-label">{t('access_roles.name')}</label>
                    <input 
                      type="text" 
                      className="form-control" 
                      value={newRole.name}
                      onChange={(e) => setNewRole({...newRole, name: e.target.value})}
                      required
                    />
                    <div className="form-text">{t('access_roles.placeholder.name')}</div>
                  </div>
                  <div className="mb-3">
                    <label className="form-label">{t('access_roles.memo')}</label>
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
                      {t('common.cancel')}
                    </button>
                    <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
                      {isSubmitting ? (
                        <>
                          <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                          {t('common.loading')}
                        </>
                      ) : t('common.confirm')}
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
                <h5 className="modal-title">{t('access_roles.delete_confirm')}</h5>
                <button 
                  type="button" 
                  className="btn-close" 
                  onClick={() => setRoleToDelete(null)}
                  disabled={isDeleting}
                ></button>
              </div>
              <div className="modal-body">
                <p>{t('access_roles.delete_message', { name: roleToDelete.name })}</p>
                <p className="text-danger">{t('access_roles.delete_warning')}</p>
              </div>
              <div className="modal-footer">
                <button 
                  type="button" 
                  className="btn btn-secondary" 
                  onClick={() => setRoleToDelete(null)}
                  disabled={isDeleting}
                >
                  {t('common.cancel')}
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
                      {t('common.loading')}
                    </>
                  ) : t('access_roles.delete_confirm')}
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
