
import { useEffect, useState } from 'react';

function App() {
  const [message, setMessage] = useState('Đang kết nối API...');
  const [error, setError] = useState('');

  useEffect(() => {
    let cancelled = false;

    async function connectAPI() {
      console.time('API total');

      try {
        const response = await fetch(
          'http://127.0.0.1:8000/api/test',
          { signal: AbortSignal.timeout(10000) }
        );

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        if (!cancelled) {
          setMessage(data.message || 'API đã phản hồi.');
          setError('');
        }
      } catch (err) {
        if (!cancelled) {
          setMessage('Kết nối API thất bại.');
          setError(err.message);
        }
      } finally {
        console.timeEnd('API total');
      }
    }

    connectAPI();

    return () => {
      cancelled = true;
    };
  }, []);

  return (
    <main style={{ padding: 30, fontFamily: 'Arial' }}>
      <h1>Quản lý nhiệm vụ và dự án</h1>
      <p>{message}</p>
      {error && <p style={{ color: 'red' }}>{error}</p>}
    </main>
  );
}

export default App;
