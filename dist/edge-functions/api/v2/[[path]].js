// ============================================
// EdgeOne Pages API 代理配置
// 修改下方 API_BASE 为你的后端 API 地址
// ============================================
const API_BASE = 'https://egouu.com';

/**
 * 代理所有 /api/v2/* 请求到目标服务器
 * 例如: /api/v2/configs → https://your-api-server.com/api/v2/configs
 */
export async function onRequest(context) {
  const { request, params } = context;
  const url = new URL(request.url);

  // 获取动态路径部分（数组格式）
  let path = '';
  if (params.path) {
    path = Array.isArray(params.path)
      ? '/' + params.path.join('/')
      : '/' + params.path;
  }

  // 拼接目标 URL（保留完整路径 /api/v2/...）
  const targetUrl = `${API_BASE}/api/v2${path}${url.search}`;

  // 复制请求头，移除可能导致问题的头
  const headers = new Headers();
  for (const [key, value] of request.headers.entries()) {
    // 跳过这些头，避免冲突
    if (['host', 'accept-encoding', 'content-length', 'transfer-encoding'].includes(key.toLowerCase())) {
      continue;
    }
    headers.set(key, value);
  }
  headers.set('X-Forwarded-Host', url.host);

  try {
    // 转发请求到目标服务器
    const response = await fetch(targetUrl, {
      method: request.method,
      headers: headers,
      body: request.method !== 'GET' && request.method !== 'HEAD'
        ? request.body
        : undefined,
    });

    // 读取响应内容（自动解压）
    const body = await response.arrayBuffer();

    // 构建新的响应头，移除编码相关头
    const responseHeaders = new Headers();
    for (const [key, value] of response.headers.entries()) {
      // 跳过编码相关头，避免解码错误
      if (['content-encoding', 'transfer-encoding', 'content-length'].includes(key.toLowerCase())) {
        continue;
      }
      responseHeaders.set(key, value);
    }
    responseHeaders.set('Access-Control-Allow-Origin', '*');

    return new Response(body, {
      status: response.status,
      statusText: response.statusText,
      headers: responseHeaders,
    });
  } catch (error) {
    return new Response(JSON.stringify({
      error: 'Proxy Error',
      message: error.message,
      target: targetUrl
    }), {
      status: 502,
      headers: { 'Content-Type': 'application/json' },
    });
  }
}

// 处理 OPTIONS 预检请求（CORS）
export function onRequestOptions() {
  return new Response(null, {
    status: 204,
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type, Authorization, X-Requested-With',
      'Access-Control-Max-Age': '86400',
    },
  });
}
