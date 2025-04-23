export const mockRoles = [
    { id: 1, code: 'superadmin', name: '超級管理員' },
    { id: 2, code: 'editor', name: '內容編輯' },
    { id: 3, code: 'viewer', name: '只讀訪客' }
  ];
  
  export const mockPermissions = [
    { id: 101, code: 'dashboard___invoke', name: '進入控制台' },
    { id: 102, code: 'post_list', name: '查看文章清單' },
    { id: 103, code: 'post_edit', name: '編輯文章' },
    { id: 104, code: 'user_manage', name: '管理使用者' }
  ];
  
  export const mockMatrix = {
    1: [101, 102, 103, 104],  // superadmin 擁有全部
    2: [101, 102, 103],       // editor 沒有 user 管理
    3: [101, 102]             // viewer 沒有編輯、user 管理
  };
  