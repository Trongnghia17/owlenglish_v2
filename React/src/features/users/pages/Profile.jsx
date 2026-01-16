import React, { useState, useEffect, useRef } from 'react';
import { Modal, Input, DatePicker } from 'antd';
import { UploadOutlined } from '@ant-design/icons';
import useAuth from '@/features/auth/store/auth.store';
import avatar_default from '@/assets/images/avatar_default.svg';
import icon_edit_avatar from '@/assets/images/icon_edit_avatar.svg';
import next_right from '@/assets/images/next-right.svg';
import moment from 'moment';
import './Profile.css';
import { updateProfile } from '../api/users.api';
const VITE_API_LARAVEL_SERVER = import.meta.env.VITE_API_BASE_URL;
import { toast } from "react-toastify";
import dayjs from 'dayjs';

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
  const avatarInputRef = useRef(null);

  useEffect(() => {
    if (user) {
      setForm({
        avatar: null,
        name: user.name || '',
        birthday: user.birthday
          ? dayjs(user.birthday, 'YYYY-MM-DD')
          : null,
        email: user.email || '',
        phone: user.phone || '',
      });
    }
  }, [user]);


  const openModal = () => setModalVisible(true);
  const closeModal = () => setModalVisible(false);

  const onEdit = (field) => {
    if (field === 'avatar') {
      avatarInputRef.current?.click();
    } else {
      openModal();
    }
  };

  const handleChange = (field, value) => {
    setForm(prev => ({ ...prev, [field]: value }));
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
      setUser(result.user);
      toast.success("Lưu thông tin thành công");
      closeModal();

    } catch (err) {
      toast.error("Lưu thông tin thất bại");
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
              onClick={() => onEdit(field)}
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
                    {field === 'birthday'
                      ? (user?.birthday
                        ? moment(user.birthday).format('DD-MM-YYYY')
                        : '—')
                      : (user?.[field] || '—')}
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
        title="Chỉnh sửa thông tin cá nhân"
        open={modalVisible}
        onCancel={closeModal}
        onOk={handleSubmit}
        confirmLoading={loading}
        okText="Lưu"
        cancelText="Hủy"
        className='modal-edit-user'
      >

        <div style={{ marginBottom: 16, marginTop: 20 }}>
          <Input value={form.name} placeholder='Họ và tên' onChange={e => handleChange('name', e.target.value)} />
        </div>

        <div style={{ marginBottom: 16 }}>
          <Input value={form.email} placeholder='Email của bạn' onChange={e => handleChange('email', e.target.value)} />
        </div>

        <div style={{ marginBottom: 16 }}>
          <Input value={form.phone} placeholder='Số điện thoại' onChange={e => handleChange('phone', e.target.value)} />
        </div>

        <div
          style={{ marginBottom: 16 }}
          onWheel={(e) => e.preventDefault()}
        >
          <DatePicker
            placeholder="Ngày sinh"
            value={form.birthday}
            onChange={(date) => handleChange('birthday', date)}
            style={{ width: '100%' }}
            inputReadOnly
          />
        </div>


      </Modal>

      <input
        type="file"
        accept="image/*"
        ref={avatarInputRef}
        style={{ display: 'none' }}
        onChange={async (e) => {
          const file = e.target.files[0];
          if (!file) return;

          if (!file.type.startsWith('image/')) {
            toast.error('Vui lòng chọn file ảnh');
            return;
          }

          try {
            setLoading(true);

            const fd = new FormData();
            fd.append('avatar', file);

            const result = await updateProfile(fd);
            setUser(result.user);

            toast.success('Cập nhật ảnh đại diện thành công');
          } catch {
            toast.error('Cập nhật ảnh thất bại');
          } finally {
            setLoading(false);
            e.target.value = '';
          }
        }}
      />

    </div>
  );
}