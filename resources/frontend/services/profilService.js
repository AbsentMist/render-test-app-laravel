import api from './api'

const profilService = {
  getProfil() {
    return api.get('/participant/profil')
  },

  updateProfil(payload) {
    const formData = new FormData()

    Object.entries(payload).forEach(([key, value]) => {
      if (value === undefined || value === null) {
        return
      }

      if (key === 'photo' && !(value instanceof File)) {
        return
      }

      formData.append(key, value)
    })

    return api.post('/participant/profil', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      params: {
        _method: 'PUT',
      },
    })
  },

  updateAuthPassword(payload) {
    return api.post('/password', payload)
  },
}

export default profilService
