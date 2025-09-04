export default function Home() {
  return (
    <div>
      <h2>Trang chủ</h2>
      <p>Chào mừng đến hệ thống OWL English. Chọn menu bên trái để bắt đầu.</p>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, minmax(0, 1fr))', gap: 16, marginTop: 16 }}>
        <div className="card">
          <h3>Khoá học</h3>
          <p>Xem danh sách khoá học, lớp và buổi học.</p>
        </div>
        <div className="card">
          <h3>Điểm danh</h3>
          <p>Quét QR, xem lịch sử điểm danh của bạn.</p>
        </div>
        <div className="card">
          <h3>Yêu cầu</h3>
          <p>Gửi yêu cầu đổi buổi / đổi lớp.</p>
        </div>
      </div>
    </div>
  );
}
