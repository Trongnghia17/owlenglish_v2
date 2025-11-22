import { useLocation, useNavigate } from "react-router-dom";
import { useState } from "react";
import { toast } from "react-toastify";
import api from "@/lib/axios";
import logo_otp from "../../../assets/images/logo-otp.png";
import imgleftlogin from "../../../assets/images/imgleftlogin.png";
import icon_otp from "../../../assets/images/icon-otp.png";
import ButtonLoading from '../../../components/common/ButtonLoading';
import './Login.css';

export default function VerifyOtp() {
    const location = useLocation();
    const nav = useNavigate();
    const { channel, destination, password, email } = location.state || {};
    const [loading, setLoading] = useState(false);
    const [otp, setOtp] = useState("");
    const handleVerify = async () => {
        if (!otp.trim()) {
            toast.error("Vui lòng nhập mã OTP");
            return;
        }
        setLoading(true);
        try {
            const res = await api.post("api/otp/verify", {
                email,
                channel,
                destination,
                otp: otp,
                purpose: "register",
                password,
            });

            toast.success("Xác thực thành công! Tài khoản đã được tạo.");
            nav("/login");
        } catch (error) {
            toast.error(error?.response?.data?.message || "Xác thực OTP thất bại");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="login-wrapper register-wrapper">
            <div className="login-imgleftmain">

            </div>
            <div className="login-cardmain">
                <div className='login-card'>
                    <div className="logo-div">
                        <img className='logo_otp' src={logo_otp} alt="logo-phi-dang" />
                    </div>
                    <h2 className='title-login'>Nhập mã OTP</h2>
                    <p>
                        Nhập mã OTP được gửi qua {channel === "email" ? "Email" : "Zalo OA"}:{" "}
                        <b>{destination}</b>
                    </p>
                    <div className='login-input-container'>
                        <input
                            className="login-input"
                            type="text"
                            placeholder="Mã OTP"
                            value={otp}
                            onChange={(e) => setOtp(e.target.value)}
                        />
                        <img className='key-logo-input' src={icon_otp} alt="email-img" />
                    </div>
                    <div className="otp-action-group">
                        <ButtonLoading
                            className="otp-submit login-primaryBtn"
                            onClick={handleVerify}
                            loading={loading}
                        >
                            Xác thực
                        </ButtonLoading>
                    </div>
                </div>
            </div>
        </div>
    );
}
