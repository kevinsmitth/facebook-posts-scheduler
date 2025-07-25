import { format, parseISO, formatDistanceToNow, isValid, isPast, isFuture, Locale } from 'date-fns'
import { ptBR } from 'date-fns/locale'

type DateInput = string | Date | number
type DateFormatOptions = {
  locale?: Locale
  addSuffix?: boolean
}

const defaultLocale = ptBR

const parseDate = (date: DateInput): Date | null => {
  try {
    if (date instanceof Date) {
      return isValid(date) ? date : null
    }

    if (typeof date === 'string') {
      if (date.includes('T') || date.includes('Z')) {
        const parsed = parseISO(date)
        return isValid(parsed) ? parsed : null
      }

      const parsed = new Date(date)
      return isValid(parsed) ? parsed : null
    }

    if (typeof date === 'number') {
      const parsed = new Date(date)
      return isValid(parsed) ? parsed : null
    }

    return null
  } catch (error) {
    return null
  }
}

export const formatDate = (
  date: DateInput,
  pattern: string = 'dd/MM/yyyy',
  options: DateFormatOptions = {},
): string => {
  try {
    const dateObj = parseDate(date)
    if (!dateObj) return 'Data inválida'

    return format(dateObj, pattern, {
      locale: options.locale || defaultLocale,
    })
  } catch (error) {
    console.warn('Erro ao formatar data:', error)
    return 'Data inválida'
  }
}

export const formatDateTime = (
  date: DateInput,
  pattern: string = 'dd/MM/yyyy HH:mm',
  options: DateFormatOptions = {},
): string => {
  return formatDate(date, pattern, options)
}

export const formatTime = (date: DateInput, pattern: string = 'HH:mm'): string => {
  return formatDate(date, pattern)
}

export const formatRelative = (date: DateInput, options: DateFormatOptions = {}): string => {
  try {
    const dateObj = parseDate(date)
    if (!dateObj) return 'Data inválida'

    return formatDistanceToNow(dateObj, {
      locale: options.locale || defaultLocale,
      addSuffix: options.addSuffix !== false,
    })
  } catch (error) {
    console.warn('Erro ao formatar data relativa:', error)
    return 'Data inválida'
  }
}

export const toISOString = (date: DateInput): string | null => {
  try {
    const dateObj = parseDate(date)
    return dateObj ? dateObj.toISOString() : null
  } catch (error) {
    console.warn('Erro ao converter para ISO:', error)
    return null
  }
}

export const toDateTimeLocalString = (date: DateInput): string | null => {
  try {
    const dateObj = parseDate(date)
    if (!dateObj) return null

    const offset = dateObj.getTimezoneOffset()
    const localDate = new Date(dateObj.getTime() - offset * 60 * 1000)

    return localDate.toISOString().slice(0, 16)
  } catch (error) {
    console.warn('Erro ao converter para datetime-local:', error)
    return null
  }
}

export const isValidDate = (date: DateInput): boolean => {
  try {
    const dateObj = parseDate(date)
    return dateObj !== null && isValid(dateObj)
  } catch (error) {
    return false
  }
}

export const isPastDate = (date: DateInput): boolean => {
  try {
    const dateObj = parseDate(date)
    return dateObj ? isPast(dateObj) : false
  } catch (error) {
    return false
  }
}

export const isFutureDate = (date: DateInput): boolean => {
  try {
    const dateObj = parseDate(date)
    return dateObj ? isFuture(dateObj) : false
  } catch (error) {
    return false
  }
}

export const now = (): string => {
  return new Date().toISOString()
}

export const nowForInput = (): string => {
  return toDateTimeLocalString(new Date()) || ''
}

export const addTime = (
  date: DateInput,
  amount: number,
  unit: 'minutes' | 'hours' | 'days' | 'weeks' | 'months' = 'days',
): Date | null => {
  try {
    const dateObj = parseDate(date)
    if (!dateObj) return null

    const result = new Date(dateObj)

    switch (unit) {
      case 'minutes':
        result.setMinutes(result.getMinutes() + amount)
        break
      case 'hours':
        result.setHours(result.getHours() + amount)
        break
      case 'days':
        result.setDate(result.getDate() + amount)
        break
      case 'weeks':
        result.setDate(result.getDate() + amount * 7)
        break
      case 'months':
        result.setMonth(result.getMonth() + amount)
        break
    }

    return result
  } catch (error) {
    console.warn('Erro ao adicionar tempo:', error)
    return null
  }
}

export const diffInDays = (date1: DateInput, date2: DateInput): number | null => {
  try {
    const dateObj1 = parseDate(date1)
    const dateObj2 = parseDate(date2)

    if (!dateObj1 || !dateObj2) return null

    const diffTime = Math.abs(dateObj2.getTime() - dateObj1.getTime())
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  } catch (error) {
    console.warn('Erro ao calcular diferença:', error)
    return null
  }
}
