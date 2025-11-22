import "./Loading.css";

export default function Loading({ fullscreen = false }) {
  if (fullscreen) {
    return (
      <div className="loading-overlay">
        <div className="loading-spinner" />
      </div>
    );
  }

  return <div className="loading-spinner" />;
}
