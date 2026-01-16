import { useNavigate } from "react-router-dom";
import { useState } from "react";
import { toast } from "react-toastify";
import api from "@/lib/axios";
import logo_otp from "../../../assets/images/logo-otp.png";
import icon_otp from "../../../assets/images/icon-otp.png";
import ButtonLoading from "../../../components/common/ButtonLoading";
import "./Login.css";

export default function ForgotPassword() {
  const nav = useNavigate();
  const [account, setAccount] = useState("");
  const [loading, setLoading] = useState(false);

  const isEmail = (value) => /\S+@\S+\.\S+/.test(value);
  const isPhone = (value) => /^[0-9]{9,11}$/.test(value);

  const handleSendOtp = async () => {
    if (!account.trim()) {
      toast.error("Vui lòng nhập email hoặc số điện thoại");
      return;
    }

    let channel = "";
    let payload = {};

    if (isEmail(account)) {
      channel = "email";
      payload = {
        channel: "email",
        destination: account,
        email: account,
        purpose: "reset_password",
      };
    } else if (isPhone(account)) {
      channel = "zalo_oa";
      payload = {
        channel: "zalo_oa",
        destination: account,
        phone: account,
        purpose: "reset_password",
      };
    } else {
      toast.error("Email hoặc số điện thoại không hợp lệ");
      return;
    }

    setLoading(true);
    try {
      const apiUrl =
        channel === "email" ? "api/otp/send" : "api/otp/send-zalo";

      const res = await api.post(apiUrl, payload);

      toast.success(res.data.message);

      nav("/verify-otp", {
        state: {
          channel,
          destination: account,
          email: isEmail(account) ? account : null,
          phone: isPhone(account) ? account : null,
          purpose: "reset_password",
        },
      });
    } catch (error) {
      toast.error(error?.response?.data?.message || "Gửi OTP thất bại");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-wrapper register-wrapper forgot-wrapper">
      <div className="login-imgleftmain"></div>

      <div className="login-cardmain">
        <div className="login-card">
          <div className="logo-div">
            <img className="logo_otp" src={logo_otp} alt="logo" />
          </div>

          <h2 className="title-login">Bạn quên mật khẩu?</h2>
          <p className="desc-login">
            Bạn vui lòng nhập email hoặc số điện thoại mà bạn đã đăng ký tài khoản.
          </p>

          <div className="login-input-container">
            <input
              className="login-input"
              type="text"
              placeholder="Email hoặc số điện thoại"
              value={account}
              onChange={(e) => setAccount(e.target.value)}
            />
            <img className="key-logo-input" src={icon_otp} alt="account" />
          </div>

          <div className="otp-action-group">
            <ButtonLoading
              className="otp-submit login-primaryBtn"
              onClick={handleSendOtp}
              loading={loading}
            >
              Gửi mã xác nhận
            </ButtonLoading>
          </div>
        </div>
      </div>
    </div>
  );
}
