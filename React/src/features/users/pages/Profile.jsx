import React, { useState, useEffect } from 'react';
import { Modal, Input, DatePicker, Button, Upload, message } from 'antd';
import { UploadOutlined } from '@ant-design/icons';
import useAuth from '@/features/auth/store/auth.store';
import avatar_default from '@/assets/images/avatar_default.svg';
import icon_edit_avatar from '@/assets/images/icon_edit_avatar.svg';
import next_right from '@/assets/images/next-right.svg';
import moment from 'moment';
import './Profile.css';
import { updateProfile } from '../api/users.api';
const VITE_API_LARAVEL_SERVER = import.meta.env.VITE_API_BASE_URL;

export default function Profile() {
  const { user, setUser } = useAuth();
  const [modalVisible, setModalVisible] = useState(false);
  const [loading, setLoading] = useState(false);
  const [form, setForm] = useState({
    avatar: null,
    name: '',
    birthday: null,
    email: '',
    phone: '',
  });

  useEffect(() => {
    if (user) {
      setForm({
        avatar: null,
        name: user.name || '',
        birthday: user.birthday ? moment(user.birthday) : null,
        email: user.email || '',
        phone: user.phone || '',
      });
    }
  }, [user]);


  const openModal = () => setModalVisible(true);
  const closeModal = () => setModalVisible(false);

  const onEdit = (field) => {
    console.log('edit', field);
  };

  const handleChange = (field, value) => {
    setForm(prev => ({ ...prev, [field]: value }));
  };

  const handleAvatarChange = ({ file }) => {
    if (file.status === 'done' || file.status === 'uploading') {
      handleChange('avatar', file.originFileObj);
    }
  };

  const handleSubmit = async () => {
    setLoading(true);

    try {
      const fd = new FormData();
      if (form.avatar) fd.append("avatar", form.avatar);

      fd.append("name", form.name ?? "");
      fd.append("birthday", form.birthday ? form.birthday.format("YYYY-MM-DD") : "");
      fd.append("email", form.email ?? "");
      fd.append("phone", form.phone ?? "");
      const result = await updateProfile(fd);
      console.log(form.name)
      setUser(result.user);
      message.success("Cập nhật thành công");
      closeModal();

    } catch (err) {
      console.log(err);
      message.error("Cập nhật thất bại");
    } finally {
      setLoading(false);
    }
  };


  const onChangePassword = () => console.log('change password');
  const onDeleteAccount = () => console.log('delete account');

  return (
    <div className="profile-page">
      <h3 className="profile-title">THÔNG TIN TÀI KHOẢN</h3>

      <div className="profile-card">
        <div className="profile-section">
          <div className="section-title">Thông tin cá nhân</div>

          {['avatar', 'name', 'birthday', 'email', 'phone'].map(field => (
            <button
              key={field}
              className="profile-row active-hover"
              onClick={openModal}
            >
              <div className="row-label">
                {field === 'avatar' ? 'Ảnh đại diện' :
                  field === 'name' ? 'Họ tên' :
                    field === 'birthday' ? 'Ngày sinh' :
                      field === 'email' ? 'Địa chỉ Email' :
                        'Số điện thoại'}
              </div>
              <div className="row-value">
                {field === 'avatar' ? (
                  <div className="avatar-cell">
                    <img src={user?.avatar_url ? (VITE_API_LARAVEL_SERVER + '/storage/' + user.avatar_url) : avatar_default} />
                    <div class="avatar-overlay"></div>
                    <div className='avatar-cell-edit-icon'>
                      <img src={icon_edit_avatar} alt="" />
                    </div>
                  </div>
                ) : (
                  <>
                    {user?.[field] || '—'}
                    <img src={next_right} alt="" />
                  </>
                )}
              </div>
            </button>
          ))}
        </div>

        <div className="profile-section">
          <div className="section-title">Mật khẩu</div>

          <button className="profile-row" onClick={onChangePassword}>
            <div className="row-label">Mật khẩu</div>
            <div className="row-value">********
              <img src={next_right} alt="" />
            </div>
          </button>
        </div>

        <div className="profile-section profile-section-end">
          <div className="section-title">Nâng cao</div>

          <button className="profile-row danger" onClick={onDeleteAccount}>
            <div className="row-label">Xoá tài khoản</div>
            <div className="row-value">
              <img src={next_right} alt="" />
            </div>
          </button>
        </div>
      </div>
      <Modal
        title="Cập nhật thông tin cá nhân"
        open={modalVisible}
        onCancel={closeModal}
        onOk={handleSubmit}
        confirmLoading={loading}
      >
        <div style={{ marginBottom: 16 }}>
          <div>Ảnh đại diện</div>
          <Upload
            beforeUpload={() => false} // không tự động upload
            showUploadList={false}
            onChange={handleAvatarChange}
          >
            <Button icon={<UploadOutlined />}>Chọn ảnh</Button>
          </Upload>
          {form.avatar && (
            <div style={{ marginTop: 8 }}>
              <img src={URL.createObjectURL(form.avatar)} alt="avatar" style={{ width: 80, height: 80, borderRadius: '50%' }} />
            </div>
          )}
        </div>

        <div style={{ marginBottom: 16 }}>
          <div>Họ tên</div>
          <Input value={form.name} onChange={e => handleChange('name', e.target.value)} />
        </div>

        <div style={{ marginBottom: 16 }}>
          <div>Ngày sinh</div>
          <DatePicker value={form.birthday} onChange={date => handleChange('birthday', date)} style={{ width: '100%' }} />
        </div>

        <div style={{ marginBottom: 16 }}>
          <div>Email</div>
          <Input value={form.email} onChange={e => handleChange('email', e.target.value)} />
        </div>

        <div style={{ marginBottom: 16 }}>
          <div>Số điện thoại</div>
          <Input value={form.phone} onChange={e => handleChange('phone', e.target.value)} />
        </div>
      </Modal>
    </div>
  );
}