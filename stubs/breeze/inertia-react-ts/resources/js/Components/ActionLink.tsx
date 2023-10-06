import { PropsWithChildren } from 'react';

export default function ActionLink({ className = '', href, children, ...props }: PropsWithChildren<{
    className?: string,
    href: string,
}>) {
    return (
        <a
            {...props}
            href={href}
            className="inline-flex items-center px-4 py-2 bg-white border border-gray-800 rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-200 hover:border-gray-600 active:border-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150"
        >
            {children}
        </a>
    );
}
