import type { Component } from 'vue'
import { ref } from 'vue'

const TOAST_LIMIT = 1
const TOAST_REMOVE_DELAY = 1000000

type ToasterToast = {
  id: string
  title?: string
  description?: string
  action?: Component
  variant?: 'default' | 'destructive' | 'success' | 'warning'
}

const actionTypes = {
  ADD_TOAST: 'ADD_TOAST',
  UPDATE_TOAST: 'UPDATE_TOAST',
  DISMISS_TOAST: 'DISMISS_TOAST',
  REMOVE_TOAST: 'REMOVE_TOAST',
} as const

let count = 0

function genId() {
  count = (count + 1) % Number.MAX_SAFE_INTEGER
  return count.toString()
}

const toasts = ref<ToasterToast[]>([])

const addToRemoveQueue = (toastId: string) => {
  if (toasts.value.find(toast => toast.id === toastId)) {
    setTimeout(() => {
      toasts.value = toasts.value.filter(toast => toast.id !== toastId)
    }, TOAST_REMOVE_DELAY)
  }
}

function reducer(state: ToasterToast[], action: any): ToasterToast[] {
  switch (action.type) {
    case actionTypes.ADD_TOAST:
      return [action.toast, ...state].slice(0, TOAST_LIMIT)

    case actionTypes.UPDATE_TOAST:
      return state.map(t => (t.id === action.toast.id ? { ...t, ...action.toast } : t))

    case actionTypes.DISMISS_TOAST: {
      const { toastId } = action

      if (toastId) {
        addToRemoveQueue(toastId)
      } else {
        state.forEach((toast) => {
          addToRemoveQueue(toast.id)
        })
      }

      return state.map(t =>
        t.id === toastId || toastId === undefined
          ? {
            ...t,
            open: false,
          }
          : t,
      )
    }
    case actionTypes.REMOVE_TOAST:
      if (action.toastId === undefined) {
        return []
      }
      return state.filter(t => t.id !== action.toastId)
  }

  return state
}

function dispatch(action: any) {
  toasts.value = reducer(toasts.value, action)
}

type Toast = Omit<ToasterToast, 'id'>

function toast({ ...props }: Toast) {
  const id = genId()

  const update = (props: ToasterToast) =>
    dispatch({
      type: actionTypes.UPDATE_TOAST,
      toast: { ...props, id },
    })
  const dismiss = () => dispatch({ type: actionTypes.DISMISS_TOAST, toastId: id })

  dispatch({
    type: actionTypes.ADD_TOAST,
    toast: {
      ...props,
      id,
      open: true,
      onOpenChange: (open: boolean) => {
        if (!open)
          dismiss()
      },
    },
  })

  return {
    id,
    dismiss,
    update,
  }
}

function useToast() {
  return {
    toast,
    dismiss: (toastId?: string) => dispatch({ type: actionTypes.DISMISS_TOAST, toastId }),
    toasts: toasts,
  }
}

export { toast, useToast }