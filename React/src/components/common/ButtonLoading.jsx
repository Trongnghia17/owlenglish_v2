import React from "react";
import "./ButtonLoading.css";

export default function ButtonLoading({ loading, children, ...props }) {
    return (
        <button {...props} disabled={loading} className={props.className}>
            {loading ? <div className="btn-spinner" /> : children}
        </button>
    );
}
