import React, { useEffect, useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import './Package.css';
import { getPaymentPackages, createPayment } from '../api/packages.api';
import Loading from '../../../components/common/Loading';
import { toast } from "react-toastify";

export default function PackageList() {
    const [packages, setPackages] = useState([]);
    const [loading, setLoading] = useState(true);
    const location = useLocation();
    const navigate = useNavigate();
    useEffect(() => {
        fetchPackages();
    }, []);

    const fetchPackages = async () => {
        try {
            const res = await getPaymentPackages();
            setPackages(res || []);
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const handlePay = async (packageId) => {
        try {
            const res = await createPayment(packageId);
            window.location.href = res.checkoutUrl;
        } catch (err) {
            console.error(err);
            toast.warning("Không thể tạo thanh toán, vui lòng thử lại");
        }
    };

    return (
        <div className="pk-container">
            <div className="pk-breadcrumb">
                <Link to="/">Trang chủ</Link> &gt; <span className='active-text'>Nạp trứng cũ</span>
            </div>
            {loading && (
                <div className="pk-loading">
                    <Loading />
                </div>
            )}

            {/* ✅ CONTENT */}
            {!loading && (
                <>
                    {packages.length === 0 ? (
                        <div className="pk-empty">
                            Hiện chưa có gói nạp nào
                        </div>
                    ) : (
                        <div className="pk-list">
                            {packages.map((item) => (
                                <div
                                    key={item.id}
                                    className={`pk-card ${item.is_featured ? 'pk-featured' : ''}`}
                                >
                                    <div className="pk-card-header">
                                        <h3>{item.name}</h3>

                                        {item.discount_percent > 0 && (
                                            <span className="pk-discount">
                                                GIẢM {item.discount_percent}%
                                            </span>
                                        )}
                                    </div>

                                    <div className="pk-price">
                                        <span className="pk-price-main">
                                            {Number(item.final_price).toLocaleString()}đ
                                        </span>
                                        <span className="pk-price-unit"> / tháng</span>
                                    </div>

                                    {item.discount_percent > 0 && (
                                        <div className="pk-price-old">
                                            {Number(item.price).toLocaleString()}đ
                                        </div>
                                    )}

                                    <button
                                        className={`pk-btn ${item.is_featured ? 'pk-btn-white' : ''}`}
                                        onClick={() => handlePay(item.id)}
                                    >
                                        Đăng ký ngay
                                    </button>

                                </div>
                            ))}
                        </div>
                    )}
                </>
            )}
        </div>
    );
}
