import { ref, reactive, computed } from 'vue'
import type { ValidationErrors } from '@/types'

type ValidationRule<T = any> = (value: T) => string | null
type ValidationRules<T> = {
  [K in keyof T]?: ValidationRule<T[K]>[]
}

interface ValidationResult {
  isValid: boolean
  errors: ValidationErrors
}

export function useFormValidation<T extends Record<string, any>>(
  initialData: T,
  rules: ValidationRules<T>,
) {
  const formData = reactive<T>({ ...initialData })
  const errors = ref<ValidationErrors>({})
  const isValidating = ref<boolean>(false)
  const hasBeenValidated = ref<boolean>(false)

  const isValid = computed<boolean>(() => {
    return Object.keys(errors.value).length === 0
  })

  const hasErrors = computed<boolean>(() => {
    return Object.keys(errors.value).length > 0
  })

  const validateField = (fieldName: keyof T): boolean => {
    const fieldRules = rules[fieldName]
    const fieldValue = formData[fieldName]

    if (!fieldRules) {
      delete errors.value[fieldName as string]
      return true
    }

    const fieldErrors: string[] = []

    for (const rule of fieldRules) {
      const error = rule(fieldValue)
      if (error) {
        fieldErrors.push(error)
      }
    }

    if (fieldErrors.length > 0) {
      errors.value[fieldName as string] = fieldErrors
      return false
    } else {
      delete errors.value[fieldName as string]
      return true
    }
  }

  const validate = async (): Promise<ValidationResult> => {
    isValidating.value = true
    hasBeenValidated.value = true

    errors.value = {}

    for (const fieldName of Object.keys(rules)) {
      validateField(fieldName as keyof T)
    }

    isValidating.value = false

    return {
      isValid: isValid.value,
      errors: errors.value,
    }
  }

  const clearValidation = (): void => {
    errors.value = {}
    hasBeenValidated.value = false
  }

  const resetForm = (): void => {
    Object.assign(formData, initialData)
    clearValidation()
  }

  const setErrors = (apiErrors: ValidationErrors): void => {
    errors.value = { ...apiErrors }
    hasBeenValidated.value = true
  }

  const getFieldError = (fieldName: keyof T): string | null => {
    const fieldErrors = errors.value[fieldName as string]
    return fieldErrors && fieldErrors.length > 0 ? fieldErrors[0] : null
  }

  const hasFieldError = (fieldName: keyof T): boolean => {
    return !!errors.value[fieldName as string]?.length
  }

  return {
    formData,
    errors,
    isValidating,
    hasBeenValidated,
    isValid,
    hasErrors,
    validateField,
    validate,
    clearValidation,
    resetForm,
    setErrors,
    getFieldError,
    hasFieldError,
  }
}

export const validationRules = {
  required: <T>(message = 'Este campo é obrigatório'): ValidationRule<T> => {
    return (value: T): string | null => {
      if (value === null || value === undefined || value === '') {
        return message
      }
      return null
    }
  },

  email: (message = 'E-mail inválido'): ValidationRule<string> => {
    return (value: string): string | null => {
      if (!value) return null
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return emailRegex.test(value) ? null : message
    }
  },

  minLength: (min: number, message?: string): ValidationRule<string> => {
    return (value: string): string | null => {
      if (!value) return null
      const actualMessage = message || `Mínimo de ${min} caracteres`
      return value.length >= min ? null : actualMessage
    }
  },

  maxLength: (max: number, message?: string): ValidationRule<string> => {
    return (value: string): string | null => {
      if (!value) return null
      const actualMessage = message || `Máximo de ${max} caracteres`
      return value.length <= max ? null : actualMessage
    }
  },

  minValue: (min: number, message?: string): ValidationRule<number> => {
    return (value: number): string | null => {
      if (value === null || value === undefined) return null
      const actualMessage = message || `Valor mínimo: ${min}`
      return value >= min ? null : actualMessage
    }
  },

  maxValue: (max: number, message?: string): ValidationRule<number> => {
    return (value: number): string | null => {
      if (value === null || value === undefined) return null
      const actualMessage = message || `Valor máximo: ${max}`
      return value <= max ? null : actualMessage
    }
  },

  pattern: (pattern: RegExp, message: string): ValidationRule<string> => {
    return (value: string): string | null => {
      if (!value) return null
      return pattern.test(value) ? null : message
    }
  },

  confirmed: (otherValue: string, message = 'Os campos não coincidem'): ValidationRule<string> => {
    return (value: string): string | null => {
      return value === otherValue ? null : message
    }
  },

  fileSize: (maxSizeMB: number, message?: string): ValidationRule<File> => {
    return (file: File): string | null => {
      if (!file) return null
      const actualMessage = message || `Arquivo deve ter no máximo ${maxSizeMB}MB`
      const maxSizeBytes = maxSizeMB * 1024 * 1024
      return file.size <= maxSizeBytes ? null : actualMessage
    }
  },

  fileType: (allowedTypes: string[], message?: string): ValidationRule<File> => {
    return (file: File): string | null => {
      if (!file) return null
      const actualMessage =
        message || `Tipo de arquivo não permitido. Permitidos: ${allowedTypes.join(', ')}`
      return allowedTypes.includes(file.type) ? null : actualMessage
    }
  },
}
