import { Book, Bookmark, BookOpen, PenLine, FileText, Library, Glasses, NotebookPen, BookMarked, BookType, Quote, Highlighter } from "lucide-react";

const icons = [
  { Icon: Book, style: { top: "8%", left: "5%", animationDelay: "0s" }, animation: "animate-float-1" },
  { Icon: Bookmark, style: { top: "15%", right: "8%", animationDelay: "1.2s" }, animation: "animate-float-2" },
  { Icon: BookOpen, style: { bottom: "12%", left: "7%", animationDelay: "0.5s" }, animation: "animate-float-3" },
  { Icon: PenLine, style: { top: "25%", left: "3%", animationDelay: "2s" }, animation: "animate-float-2" },
  { Icon: FileText, style: { bottom: "20%", right: "5%", animationDelay: "0.8s" }, animation: "animate-float-1" },
  { Icon: Library, style: { top: "6%", right: "20%", animationDelay: "1.5s" }, animation: "animate-float-3" },
  { Icon: Glasses, style: { bottom: "8%", right: "15%", animationDelay: "2.2s" }, animation: "animate-float-2" },
  { Icon: NotebookPen, style: { top: "40%", left: "2%", animationDelay: "0.3s" }, animation: "animate-float-1" },
  { Icon: BookMarked, style: { bottom: "35%", right: "3%", animationDelay: "1.8s" }, animation: "animate-float-3" },
  { Icon: BookType, style: { top: "5%", left: "25%", animationDelay: "2.5s" }, animation: "animate-float-2" },
  { Icon: Quote, style: { bottom: "5%", left: "20%", animationDelay: "0.7s" }, animation: "animate-float-1" },
  { Icon: Highlighter, style: { top: "55%", right: "2%", animationDelay: "1s" }, animation: "animate-float-3" },
  { Icon: Book, style: { bottom: "25%", left: "2%", animationDelay: "1.7s" }, animation: "animate-float-2" },
  { Icon: Bookmark, style: { top: "70%", right: "6%", animationDelay: "0.4s" }, animation: "animate-float-1" },
  { Icon: BookOpen, style: { top: "12%", left: "12%", animationDelay: "2.8s" }, animation: "animate-float-3" },
  { Icon: PenLine, style: { bottom: "40%", right: "8%", animationDelay: "1.3s" }, animation: "animate-float-2" },
];

const FloatingIcons = () => {
  return (
    <div className="pointer-events-none fixed inset-0 overflow-hidden">
      {icons.map(({ Icon, style, animation }, index) => (
        <div
          key={index}
          className={`absolute ${animation}`}
          style={{
            ...style,
            opacity: 0.10 + (index % 3) * 0.03,
          }}
        >
          <Icon 
            className="text-primary" 
            size={20 + (index % 4) * 4} 
            strokeWidth={1.2}
            style={{ animationDelay: style.animationDelay }}
          />
        </div>
      ))}
    </div>
  );
};

export default FloatingIcons;
