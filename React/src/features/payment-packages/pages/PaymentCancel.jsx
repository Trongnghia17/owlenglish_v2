import { useEffect , useRef } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import { cancelPayment } from "../api/packages.api";

export default function PaymentCancel() {
    const { orderCode } = useParams();
    const navigate = useNavigate();
    const ranRef = useRef(false);
    useEffect(() => {
        if (ranRef.current) return;
        ranRef.current = true;

        const run = async () => {
            try {
                await cancelPayment(orderCode);
                toast.info("Bạn đã huỷ thanh toán");
            } catch {
                toast.error("Không thể xử lý huỷ thanh toán");
            } finally {
                navigate("/goi-nap-tien", { replace: true });
            }
        };
        run();
    }, [orderCode]);


    return null;
}
