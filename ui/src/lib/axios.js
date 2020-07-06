import {default as BaseAxios} from 'axios'

export const apiBaseUrl = process.env.NODE_ENV === 'production'
  ? `${window.location.protocol}//${window.location.host}/api/v1`
  : `http://${window.location.hostname}:8081/api/v1`

export const axios = BaseAxios.create({
    baseURL: apiBaseUrl
})
