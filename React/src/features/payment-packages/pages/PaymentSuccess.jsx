import { useEffect, useRef } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import { successPayment } from "../api/packages.api";

export default function PaymentSuccess() {
    const { orderCode } = useParams();
    const navigate = useNavigate();
    const ranRef = useRef(false);

    useEffect(() => {
        if (ranRef.current) return;
        ranRef.current = true;

        const run = async () => {
            try {
                await successPayment(orderCode);
                toast.success("Nạp cú thành công");
            } catch (err) {
                toast.error("Thanh toán chưa hoàn tất");
            } finally {
                navigate("/goi-nap-tien", { replace: true });
            }
        };

        run();
    }, [orderCode]);

    return null;
}
