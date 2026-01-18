import { useState, useEffect } from 'react';
import { Menu, Search, BookMarked, Heart, User } from 'lucide-react';

interface HeaderProps {
  onSearchClick: () => void;
}

const Header = ({ onSearchClick }: HeaderProps) => {
  const [isScrolled, setIsScrolled] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <header
      className={`sticky top-0 z-50 w-full border-b border-border/50 bg-background/95 backdrop-blur-sm transition-all duration-300 ${
        isScrolled ? 'h-14' : 'h-16'
      }`}
    >
      <div className="container mx-auto flex h-full items-center justify-between px-4">
        {/* Right: Menu */}
        <button
          className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-foreground/80 transition-colors hover:bg-accent hover:text-foreground"
          aria-label="فتح القائمة"
        >
          <Menu className="h-5 w-5" />
          <span className="hidden sm:inline">الأقسام</span>
        </button>

        {/* Center: Mini search */}
        <button
          onClick={onSearchClick}
          className="flex items-center gap-2 rounded-full border border-border bg-muted/50 px-4 py-2 text-sm text-muted-foreground transition-all hover:border-primary/30 hover:bg-muted"
          aria-label="بحث سريع"
        >
          <Search className="h-4 w-4" />
          <span>بحث سريع...</span>
        </button>

        {/* Left: Actions */}
        <div className="flex items-center gap-1 sm:gap-2">
          <button
            className="flex items-center gap-1.5 rounded-lg px-2 py-2 text-sm text-foreground/70 transition-colors hover:bg-accent hover:text-foreground sm:px-3"
            aria-label="قائمة القراءة"
          >
            <BookMarked className="h-4 w-4" />
            <span className="hidden md:inline">قائمة القراءة</span>
          </button>
          <button
            className="flex items-center gap-1.5 rounded-lg px-2 py-2 text-sm text-foreground/70 transition-colors hover:bg-accent hover:text-foreground sm:px-3"
            aria-label="المفضلة"
          >
            <Heart className="h-4 w-4" />
            <span className="hidden md:inline">المفضلة</span>
          </button>
          <button
            className="flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
            aria-label="تسجيل الدخول"
          >
            <User className="h-4 w-4" />
            <span className="hidden sm:inline">دخول</span>
          </button>
        </div>
      </div>
    </header>
  );
};

export default Header;
