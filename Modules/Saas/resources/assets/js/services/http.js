export class ApiError extends Error {
  constructor(message, status, payload = {}) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.payload = payload;
  }
}

export async function requestJson(url, options = {}) {
  let response;

  try {
    response = await fetch(url, {
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        ...(options.headers || {}),
      },
      ...options,
    });
  } catch (error) {
    throw new ApiError(error instanceof Error ? error.message : 'Network request failed.', 0);
  }

  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new ApiError(payload.message || `Request failed with status ${response.status}.`, response.status, payload);
  }

  return payload;
}
