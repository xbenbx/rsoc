import request from '@/utils/request'

export function getRsocs(query) {
  return request({
    url: '/rsoc',
    method: 'get',
    params: query
  })
}

export function getRsocById(id) {
  return request({
    url: `/rsoc/${id}`,
    method: 'get'
  })
}

export function createRsoc(data) {
  return request({
    url: '/rsoc',
    method: 'post',
    data
  })
}

export function updateRsoc(id, data) {
  return request({
    url: `/rsoc/${id}`,
    method: 'put',
    data
  })
}

export function deleteRsoc(id) {
  return request({
    url: `/rsoc/${id}`,
    method: 'delete'
  })
}

export function getRsocPerformance(id, query) {
  return request({
    url: `/rsoc/${id}/performance`,
    method: 'get',
    params: query
  })
}
