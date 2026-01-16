import React, { useEffect, useState } from 'react';
import api from '@/lib/axios';
import cong from '@/assets/images/cong.svg';
import './PaymentHistory.css';
import { Link } from 'react-router-dom';
import { getStudentPayment } from '../api/users.api';
import Loading from '../../../components/common/Loading';

export default function PaymentHistory() {
  const [loading, setLoading] = useState(true);
  const [rows, setRows] = useState([]);

  useEffect(() => {
    let mounted = true;

    (async () => {
      try {
        const res = await getStudentPayment();
        if (!mounted) return;
        setRows(Array.isArray(res) ? res : []);
      } catch (err) {
        if (!mounted) return;
        setRows([]);
      } finally {
        if (mounted) setLoading(false);
      }
    })();

    return () => {
      mounted = false;
    };
  }, []);

  const statusLabel = (status) => {
    switch (status) {
      case 'success':
        return <span className="ph-badge ph-badge--done">THÀNH CÔNG</span>;
      case 'pending':
        return <span className="ph-badge ph-badge--pending">CHỜ THANH TOÁN</span>;
      case 'failed':
        return <span className="ph-badge ph-badge--failed">THẤT BẠI</span>;
      case 'canceled':
        return <span className="ph-badge ph-badge--failed">ĐÃ HỦY</span>;
      case 'expired':
        return <span className="ph-badge ph-badge--failed">HẾT HẠN</span>;
      default:
        return <span className="ph-badge">---</span>;
    }
  };

  const formatMoney = (amount, currency) => {
    if (!amount) return '--';
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: currency || 'VND',
    }).format(amount);
  };

  return (
    <div className="payment-history">
      <div className="ph-header">
        <h3 className="ph-title">LỊCH SỬ THANH TOÁN</h3>
        <div className="ph-actions">
          <Link to="/goi-nap-tien" className="ph-btn ph-btn--primary">
            <img src={cong} alt="Nạp tiền" />
            <p className="ph-btn-text">Nạp tiền</p>
          </Link>
        </div>
      </div>

      <div className="">
        {loading ? (
         <Loading />
        ) : (
          <div className="ph-table-wrap">
            <table className="ph-table">
              <thead>
                <tr>
                  <th>Mã giao dịch</th>
                  <th>Thời gian</th>
                  <th>Số Trứng Cú</th>
                  <th>Nội dung</th>
                  <th>Trạng thái</th>
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 ? (
                  <tr className="ph-empty">
                    <td colSpan="5">Chưa có lịch sử thanh toán.</td>
                  </tr>
                ) : (
                  rows.map((r) => (
                    <tr key={r.id}>
                      <td className="ph-td-id">{r.order_code || r.id}</td>
                      <td>{r.paid_at}</td>
                      <td className="ph-td-amount" style={{fontWeight: 600}}>
                       {r.amount}
                      </td>
                      <td className="ph-td-package">
                        Thanh toán {r.package_name || '--'}
                      </td>
                      <td className="ph-td-status">
                        {statusLabel(r.status)}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
