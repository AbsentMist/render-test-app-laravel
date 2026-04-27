import { describe, test, expect, vi, beforeEach } from 'vitest'

vi.mock('../../services/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}))

import api from '../../services/api'
import profilService from '../../services/profilService'

describe('profilService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    api.get.mockResolvedValue({ data: {} })
    api.post.mockResolvedValue({ data: {} })
  })

  test('getProfil appelle la route profil participant', async () => {
    await profilService.getProfil()

    expect(api.get).toHaveBeenCalledTimes(1)
    expect(api.get).toHaveBeenCalledWith('/participant/profil')
  })

  test('updateAuthPassword appelle la route password avec le payload', async () => {
    const payload = {
      currentPassword: 'CurrentPwd!123',
      newPassword: 'NewStrongPwd!456',
      newPassword_confirmation: 'NewStrongPwd!456',
    }

    await profilService.updateAuthPassword(payload)

    expect(api.post).toHaveBeenCalledTimes(1)
    expect(api.post).toHaveBeenCalledWith('/password', payload)
  })

  test('updateProfil envoie un multipart avec _method=PUT', async () => {
    const file = new File(['img'], 'avatar.png', { type: 'image/png' })
    const payload = {
      nom: 'Dupont',
      prenom: 'Alice',
      email: 'alice.dupont@test.ch',
      photo: file,
      club: null,
      telephone: undefined,
    }

    await profilService.updateProfil(payload)

    expect(api.post).toHaveBeenCalledTimes(1)

    const [url, formData, config] = api.post.mock.calls[0]

    expect(url).toBe('/participant/profil')
    expect(formData).toBeInstanceOf(FormData)
    expect(formData.get('nom')).toBe('Dupont')
    expect(formData.get('prenom')).toBe('Alice')
    expect(formData.get('email')).toBe('alice.dupont@test.ch')
    expect(formData.get('photo')).toBe(file)

    expect(config).toEqual({
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      params: {
        _method: 'PUT',
      },
    })
  })

  test('updateProfil ignore photo si ce n est pas un fichier', async () => {
    const payload = {
      nom: 'Dupont',
      photo: 'data:image/jpeg;base64,QUJD',
    }

    await profilService.updateProfil(payload)

    const [, formData] = api.post.mock.calls[0]
    expect(formData.get('nom')).toBe('Dupont')
    expect(formData.get('photo')).toBe(null)
  })
})
