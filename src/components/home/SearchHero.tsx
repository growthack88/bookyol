import { useState, useEffect, useRef, useCallback } from 'react';
import { Search, X } from 'lucide-react';
import { useDebouncedValue } from '@/hooks/useDebouncedValue';
import { books, authors, categories, trendingSearches, searchPlaceholders } from '@/data/mockData';

const scopeOptions = [
  { id: 'books', label: 'كتب', icon: '📚' },
  { id: 'authors', label: 'مؤلفون', icon: '✍️' },
  { id: 'lists', label: 'قوائم', icon: '🗂️' },
  { id: 'quotes', label: 'اقتباسات', icon: '💬' },
  { id: 'ideas', label: 'أفكار', icon: '🧠' },
];

const SearchHero = () => {
  const [query, setQuery] = useState('');
  const [activeScope, setActiveScope] = useState('books');
  const [placeholderIndex, setPlaceholderIndex] = useState(0);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [selectedIndex, setSelectedIndex] = useState(-1);
  const debouncedQuery = useDebouncedValue(query, 200);
  const inputRef = useRef<HTMLInputElement>(null);
  const containerRef = useRef<HTMLDivElement>(null);

  // Rotate placeholders
  useEffect(() => {
    const interval = setInterval(() => {
      setPlaceholderIndex((prev) => (prev + 1) % searchPlaceholders.length);
    }, 3000);
    return () => clearInterval(interval);
  }, []);

  // Generate suggestions based on query
  const suggestions = useCallback(() => {
    if (!debouncedQuery) return { books: [], authors: [], categories: [] };
    
    const q = debouncedQuery.toLowerCase();
    return {
      books: books.filter(b => b.title.includes(q) || b.author.includes(q)).slice(0, 5),
      authors: authors.filter(a => a.name.includes(q)).slice(0, 5),
      categories: categories.filter(c => c.name.includes(q)).slice(0, 5),
    };
  }, [debouncedQuery]);

  const { books: suggestedBooks, authors: suggestedAuthors, categories: suggestedCategories } = suggestions();
  const totalSuggestions = suggestedBooks.length + suggestedAuthors.length + suggestedCategories.length;

  // Handle keyboard navigation
  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (!showSuggestions) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setSelectedIndex(prev => Math.min(prev + 1, totalSuggestions - 1));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setSelectedIndex(prev => Math.max(prev - 1, -1));
    } else if (e.key === 'Escape') {
      setShowSuggestions(false);
      setSelectedIndex(-1);
    } else if (e.key === 'Enter' && selectedIndex >= 0) {
      e.preventDefault();
      // Handle selection
      setShowSuggestions(false);
    }
  };

  // Close on outside click
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setShowSuggestions(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  return (
    <section className="relative py-12 md:py-20">
      <div className="container mx-auto px-4 text-center">
        {/* Logo */}
        <h1 className="mb-4 text-4xl font-bold tracking-tight text-foreground md:text-5xl lg:text-6xl">
          <span className="bg-gradient-to-l from-primary to-primary/70 bg-clip-text text-transparent">
            BookYol
          </span>
        </h1>
        
        {/* Tagline */}
        <p className="mb-8 text-lg text-muted-foreground md:text-xl">
          مرجع الكتب العربي… افهم الكتاب في دقيقة.
        </p>

        {/* Search Bar */}
        <div ref={containerRef} className="relative mx-auto max-w-2xl">
          <div className="relative flex items-center overflow-hidden rounded-2xl border-2 border-border bg-card shadow-lg transition-all focus-within:border-primary/50 focus-within:shadow-xl">
            <Search className="mr-4 h-5 w-5 text-muted-foreground" />
            <input
              ref={inputRef}
              type="text"
              value={query}
              onChange={(e) => {
                setQuery(e.target.value);
                setShowSuggestions(true);
                setSelectedIndex(-1);
              }}
              onFocus={() => setShowSuggestions(true)}
              onKeyDown={handleKeyDown}
              placeholder={searchPlaceholders[placeholderIndex]}
              className="h-12 flex-1 bg-transparent text-base outline-none placeholder:text-muted-foreground/60 md:h-14 md:text-lg"
              aria-label="ابحث عن كتب أو مؤلفين"
            />
            {query && (
              <button
                onClick={() => setQuery('')}
                className="p-2 text-muted-foreground hover:text-foreground"
                aria-label="مسح البحث"
              >
                <X className="h-4 w-4" />
              </button>
            )}
            <button className="m-1.5 rounded-xl bg-primary px-5 py-2.5 font-medium text-primary-foreground transition-colors hover:bg-primary/90 md:px-6 md:py-3">
              ابحث
            </button>
          </div>

          {/* Suggestions Dropdown */}
          {showSuggestions && debouncedQuery && totalSuggestions > 0 && (
            <div className="absolute left-0 right-0 top-full z-50 mt-2 overflow-hidden rounded-xl border border-border bg-card shadow-xl">
              {suggestedBooks.length > 0 && (
                <div className="border-b border-border p-2">
                  <p className="mb-2 px-3 text-xs font-medium text-muted-foreground">كتب</p>
                  {suggestedBooks.map((book, idx) => (
                    <button
                      key={book.id}
                      className={`flex w-full items-center gap-3 rounded-lg px-3 py-2 text-right transition-colors hover:bg-accent ${
                        selectedIndex === idx ? 'bg-accent' : ''
                      }`}
                    >
                      <div className={`h-10 w-7 rounded bg-gradient-to-br ${book.coverGradient}`} />
                      <div>
                        <p className="font-medium text-foreground">{book.title}</p>
                        <p className="text-sm text-muted-foreground">{book.author}</p>
                      </div>
                    </button>
                  ))}
                </div>
              )}
              {suggestedAuthors.length > 0 && (
                <div className="border-b border-border p-2">
                  <p className="mb-2 px-3 text-xs font-medium text-muted-foreground">مؤلفون</p>
                  {suggestedAuthors.map((author, idx) => (
                    <button
                      key={author.id}
                      className={`flex w-full items-center gap-3 rounded-lg px-3 py-2 text-right transition-colors hover:bg-accent ${
                        selectedIndex === suggestedBooks.length + idx ? 'bg-accent' : ''
                      }`}
                    >
                      <div className={`flex h-8 w-8 items-center justify-center rounded-full ${author.avatarColor} text-sm font-bold text-white`}>
                        {author.name.charAt(0)}
                      </div>
                      <span className="font-medium text-foreground">{author.name}</span>
                    </button>
                  ))}
                </div>
              )}
              {suggestedCategories.length > 0 && (
                <div className="p-2">
                  <p className="mb-2 px-3 text-xs font-medium text-muted-foreground">تصنيفات</p>
                  {suggestedCategories.map((cat, idx) => (
                    <button
                      key={cat.name}
                      className={`flex w-full items-center gap-3 rounded-lg px-3 py-2 text-right transition-colors hover:bg-accent ${
                        selectedIndex === suggestedBooks.length + suggestedAuthors.length + idx ? 'bg-accent' : ''
                      }`}
                    >
                      <span className="text-lg">{cat.icon}</span>
                      <span className="font-medium text-foreground">{cat.name}</span>
                      <span className="mr-auto text-sm text-muted-foreground">{cat.count} كتاب</span>
                    </button>
                  ))}
                </div>
              )}
            </div>
          )}
        </div>

        {/* Scope Pills */}
        <div className="mt-6 flex flex-wrap justify-center gap-2">
          {scopeOptions.map((scope) => (
            <button
              key={scope.id}
              onClick={() => setActiveScope(scope.id)}
              className={`rounded-full px-4 py-2 text-sm font-medium transition-all hover:scale-[1.03] ${
                activeScope === scope.id
                  ? 'bg-primary text-primary-foreground ring-2 ring-primary/30'
                  : 'bg-muted text-muted-foreground hover:bg-muted/80'
              }`}
            >
              <span className="ml-1.5">{scope.icon}</span>
              {scope.label}
            </button>
          ))}
        </div>

        {/* Trending Today */}
        <div className="mt-8">
          <p className="mb-3 text-sm text-muted-foreground">الأكثر بحثًا اليوم:</p>
          <div className="flex flex-wrap justify-center gap-2">
            {trendingSearches.map((term) => (
              <button
                key={term}
                className="rounded-full border border-border bg-card px-3 py-1.5 text-sm text-foreground transition-all hover:border-primary/30 hover:bg-accent hover:scale-[1.03]"
              >
                {term}
              </button>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

export default SearchHero;
